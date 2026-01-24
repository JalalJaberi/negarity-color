<?php

declare(strict_types=1);

namespace Negarity\Color;

use Negarity\Color\ColorSpace\{
    ColorSpaceInterface,
    RGB,
    RGBA,
    CMYK,
    HSL,
    HSLA,
    HSV,
    Lab,
    LCh,
    XYZ,
    YCbCr
};
use Negarity\Color\Registry\NamedColorRegistryInterface;
use Negarity\Color\Registry\ColorSpaceRegistry;
use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEObserver;
use Negarity\Color\Exception\UnsupportedColorSpaceException;
use Negarity\Color\Exception\ColorSpaceNotFoundException;
use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\Exception\ConversionNotSupportedException;

final class Color extends AbstractColor
{
    /**
     * Create a new Color instance.
     * 
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @param array<string, float|int> $values
     * @param CIEIlluminant|null $illuminant CIE standard illuminant (default: D65)
     * @param CIEObserver|null $observer CIE standard observer (default: TwoDegree)
     * @return void
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $colorSpace, 
        array $values = [],
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null,
        ?bool $strictClamping = null
    ) {
        parent::__construct($colorSpace, $values, $illuminant, $observer, $strictClamping);
    }

    #[\Override]
    public function without(array $channels): static
    {
        $values = $this->values;
        foreach ($channels as $channel) {
            if (!in_array($channel, $this->getChannels(), true)) {
                throw new \InvalidArgumentException("Channel '{$channel}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
            $values[$channel] = $this->colorSpace::getChannelDefaultValue($channel);
        }

        return new self($this->colorSpace, $values, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function with(array $channels): static
    {
        $values = $this->values;
        
        foreach ($channels as $channel => $value) {
            if (!in_array($channel, $this->getChannels(), true)) {
                throw new \InvalidArgumentException("Channel '{$channel}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
            $type = gettype($value);
            if ($type !== 'integer' && $type !== 'double' && $type !== 'float') {
                throw new \InvalidArgumentException("Channel '{$channel}' must be of type int or float.");
            }
            // Convert to float and always store original value (never clamp in storage)
            // Type validation already done above (int/float check)
            // Range validation: In strict mode we clamp on-the-fly, in non-strict we allow out-of-range
            $floatValue = (float)$value;
            $values[$channel] = $floatValue;
        }

        // Create new instance with same strict clamping mode
        return new self($this->colorSpace, $values, $this->illuminant, $this->observer, $this->strictClamping);
    }

    /**
     * Dynamically handle to{ColorSpace}() method calls.
     * 
     * @param string $name Method name (e.g., "toRGB", "toRGBA")
     * @param array<int, mixed> $arguments Method arguments (alpha, illuminant, observer)
     * @return static
     * @throws ColorSpaceNotFoundException
     * @throws InvalidColorValueException
     * @throws ConversionNotSupportedException
     */
    #[\Override]
    public function __call(string $name, array $arguments): mixed
    {
        // Check if this is a to{ColorSpace}() call
        if (str_starts_with($name, 'to') && strlen($name) > 2) {
            $colorSpaceName = strtolower(substr($name, 2));
            
            // Check if color space is registered
            if (!ColorSpaceRegistry::has($colorSpaceName)) {
                // Not a registered color space, fall back to parent (getters, filters)
                return parent::__call($name, $arguments);
            }
            
            try {
                $targetSpaceClass = ColorSpaceRegistry::get($colorSpaceName);
            } catch (ColorSpaceNotFoundException $e) {
                throw $e;
            }
            
            // Extract parameters from arguments
            $alpha = null;
            $illuminant = null;
            $observer = null;
            
            // Check first argument for alpha (int between 0-255)
            if (!empty($arguments) && is_int($arguments[0]) && $arguments[0] >= 0 && $arguments[0] <= 255) {
                $alpha = $arguments[0];
            }
            
            // Scan all arguments for CIE parameters
            foreach ($arguments as $arg) {
                if ($arg instanceof CIEIlluminant) {
                    $illuminant = $arg;
                } elseif ($arg instanceof CIEObserver) {
                    $observer = $arg;
                }
            }
            
            // Validate and log warnings for unsupported parameters
            if ($illuminant !== null && !$targetSpaceClass::supportsIlluminant()) {
                error_log(
                    sprintf(
                        "[Negarity Color] Warning: Illuminant parameter provided but color space '%s' does not support it. Ignoring.",
                        $colorSpaceName
                    )
                );
                $illuminant = null;
            }
            
            if ($observer !== null && !$targetSpaceClass::supportsObserver()) {
                error_log(
                    sprintf(
                        "[Negarity Color] Warning: Observer parameter provided but color space '%s' does not support it. Ignoring.",
                        $colorSpaceName
                    )
                );
                $observer = null;
            }
            
            // Use instance values if not provided
            $illuminant = $illuminant ?? $this->illuminant;
            $observer = $observer ?? $this->observer;
            
            // Convert to target color space
            $result = $this->convertToColorSpace($colorSpaceName, $illuminant, $observer);
            $values = $result['values'];
            
            // Handle alpha channel
            if ($targetSpaceClass::supportAlphaChannel()) {
                $alphaChannelName = $targetSpaceClass::getAlphaChannelName();
                
                if ($alpha !== null) {
                    // Validate alpha range
                    if ($alpha < 0 || $alpha > 255) {
                        throw new InvalidColorValueException('Alpha value must be between 0 and 255');
                    }
                    $values[$alphaChannelName] = (float)$alpha;
                } elseif (!isset($values[$alphaChannelName])) {
                    // If alpha not provided and not in result, preserve from source if available
                    $currentAlphaChannel = $this->colorSpace::getAlphaChannelName();
                    if ($currentAlphaChannel !== '' && isset($this->values[$currentAlphaChannel])) {
                        $values[$alphaChannelName] = $this->values[$currentAlphaChannel];
                    } else {
                        // Default to 255 (fully opaque)
                        $values[$alphaChannelName] = 255.0;
                    }
                }
            } elseif ($alpha !== null) {
                // Alpha provided but target doesn't support it
                error_log(
                    sprintf(
                        "[Negarity Color] Warning: Alpha parameter provided but color space '%s' does not support alpha channel. Ignoring.",
                        $colorSpaceName
                    )
                );
            }
            
            // Create new instance with converted values
            return new self($targetSpaceClass, $values, $illuminant, $observer, $result['strictMode']);
        }
        
        // Not a to{ColorSpace}() call, delegate to parent (getters, filters)
        return parent::__call($name, $arguments);
    }

    #[\Override]
    public function withIlluminant(CIEIlluminant $illuminant): static
    {
        if (!$this->colorSpace::supportsIlluminant()) {
            throw new UnsupportedColorSpaceException(
                "Color space '{$this->getColorSpaceName()}' does not support illuminants."
            );
        }
        return new self($this->colorSpace, $this->values, $illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function adaptIlluminant(
        CIEIlluminant $targetIlluminant,
        ?\Negarity\Color\CIE\AdaptationMethod $method = null
    ): static {
        if (!$this->colorSpace::supportsIlluminant()) {
            throw new UnsupportedColorSpaceException(
                "Color space '{$this->getColorSpaceName()}' does not support illuminants."
            );
        }

        $method = $method ?? \Negarity\Color\CIE\AdaptationMethod::Bradford;

        // Convert to XYZ with current illuminant
        $xyz = $this->toXYZ();
        $xyzValues = ['x' => $xyz->getX(), 'y' => $xyz->getY(), 'z' => $xyz->getZ()];

        // Perform chromatic adaptation
        $adaptedXyz = $this->performChromaticAdaptation(
            $xyzValues,
            $this->illuminant,
            $targetIlluminant,
            $this->observer,
            $method
        );

        // Convert adapted XYZ back to original color space with new illuminant
        $originalSpaceClass = $this->colorSpace;
        $originalSpaceName = $this->getColorSpaceName();
        
        // Convert XYZ to RGB first, then to original space
        $rgbValues = \Negarity\Color\ColorSpace\XYZ::toRGB($adaptedXyz, $targetIlluminant, $this->observer);
        $adaptedValues = $originalSpaceClass::fromRGB($rgbValues, 255, $targetIlluminant, $this->observer);

        return new self($originalSpaceClass, $adaptedValues, $targetIlluminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function adaptObserver(CIEObserver $targetObserver): static
    {
        if (!$this->colorSpace::supportsObserver()) {
            throw new UnsupportedColorSpaceException(
                "Color space '{$this->getColorSpaceName()}' does not support observers."
            );
        }

        // Convert to RGB (observer-independent intermediate space)
        $rgb = $this->toRGB();
        $rgbValues = ['r' => $rgb->getR(), 'g' => $rgb->getG(), 'b' => $rgb->getB()];

        // Convert RGB back to original color space with new observer
        // This will use the new observer's reference white for Lab/XYZ conversions
        $originalSpaceClass = $this->colorSpace;
        $adaptedValues = $originalSpaceClass::fromRGB($rgbValues, 255, $this->illuminant, $targetObserver);

        return new self($originalSpaceClass, $adaptedValues, $this->illuminant, $targetObserver, $this->strictClamping);
    }
}

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

    public function toRGB(): static
    {
        $result = $this->convertToColorSpace('rgb');
        return new self(RGB::class, $result['values'], $this->illuminant, $this->observer, $result['strictMode']);
    }

    public function toRGBA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new InvalidColorValueException('Alpha value must be between 0 and 255');
        }

        // If already RGBA, return self (or update alpha if different)
        if ($this->colorSpace === RGBA::class) {
            if ($this->getA() === $alpha) {
                return new self($this->colorSpace, $this->values, $this->illuminant, $this->observer, $this->strictClamping);
            }
            // Update alpha
            $values = $this->values;
            $values['a'] = $alpha;
            return new self($this->colorSpace, $values, $this->illuminant, $this->observer, $this->strictClamping);
        }

        // If HSLA, preserve alpha
        if ($this->colorSpace === HSLA::class) {
            $alpha = $this->getA();
        }

        // Convert to RGB first, then to RGBA
        $rgb = $this->toRGB();
        $rgbaValues = RGBA::fromRGB($rgb->values, $alpha);
        try {
            $rgbaClass = ColorSpaceRegistry::get('rgba');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "RGBA color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        // Use strict mode from RGB conversion (non-strict if indirect to preserve precision)
        $strictMode = $rgb->strictClamping;
        return new self($rgbaClass, $rgbaValues, $this->illuminant, $this->observer, $strictMode);
    }

    public function toCMYK(): static
    {
        $result = $this->convertToColorSpace('cmyk');
        try {
            $cmykClass = ColorSpaceRegistry::get('cmyk');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "CMYK color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        return new self($cmykClass, $result['values'], $this->illuminant, $this->observer, $result['strictMode']);
    }

    public function toHSL(): static
    {
        $result = $this->convertToColorSpace('hsl');
        try {
            $hslClass = ColorSpaceRegistry::get('hsl');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "HSL color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        return new self($hslClass, $result['values'], $this->illuminant, $this->observer, $result['strictMode']);
    }

    public function toHSLA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new InvalidColorValueException('Alpha value must be between 0 and 255');
        }

        // If already HSLA, return self (or update alpha if different)
        if ($this->colorSpace === HSLA::class) {
            if ($this->getA() === $alpha) {
                return new self($this->colorSpace, $this->values, $this->illuminant, $this->observer, $this->strictClamping);
            }
            // Update alpha
            $values = $this->values;
            $values['a'] = $alpha;
            return new self($this->colorSpace, $values, $this->illuminant, $this->observer, $this->strictClamping);
        }

        // If HSL, convert to HSLA with specified alpha
        if ($this->colorSpace === HSL::class) {
            $values = $this->values;
            $values['a'] = $alpha;
            try {
                $hslaClass = ColorSpaceRegistry::get('hsla');
            } catch (ColorSpaceNotFoundException $e) {
                throw new ColorSpaceNotFoundException(
                    "HSLA color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                    0,
                    $e
                );
            }
            return new self($hslaClass, $values, $this->illuminant, $this->observer, $this->strictClamping);
        }

        // If RGBA, preserve alpha
        if ($this->colorSpace === RGBA::class) {
            $alpha = $this->getA();
        }

        // Convert to HSL first, then to HSLA
        $hsl = $this->toHSL();
        $hslaValues = HSLA::fromRGB($hsl->toRGB()->values, $alpha);
        try {
            $hslaClass = ColorSpaceRegistry::get('hsla');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "HSLA color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        // Use strict mode from HSL conversion (non-strict if indirect to preserve precision)
        $strictMode = $hsl->strictClamping;
        return new self($hslaClass, $hslaValues, $this->illuminant, $this->observer, $strictMode);
    }

    public function toHSV(): static
    {
        $result = $this->convertToColorSpace('hsv');
        try {
            $hsvClass = ColorSpaceRegistry::get('hsv');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "HSV color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        return new self($hsvClass, $result['values'], $this->illuminant, $this->observer, $result['strictMode']);
    }

    public function toLab(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $result = $this->convertToColorSpace('lab', $illuminant, $observer);
        try {
            $labClass = ColorSpaceRegistry::get('lab');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "Lab color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        return new self($labClass, $result['values'], $illuminant, $observer, $result['strictMode']);
    }

    public function toLCh(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $result = $this->convertToColorSpace('lch', $illuminant, $observer);
        try {
            $lchClass = ColorSpaceRegistry::get('lch');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "LCh color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        return new self($lchClass, $result['values'], $illuminant, $observer, $result['strictMode']);
    }

    public function toXYZ(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $result = $this->convertToColorSpace('xyz', $illuminant, $observer);
        try {
            $xyzClass = ColorSpaceRegistry::get('xyz');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "XYZ color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        return new self($xyzClass, $result['values'], $illuminant, $observer, $result['strictMode']);
    }

    public function toYCbCr(): static
    {
        $result = $this->convertToColorSpace('ycbcr');
        try {
            $ycbcrClass = ColorSpaceRegistry::get('ycbcr');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "YCbCr color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        return new self($ycbcrClass, $result['values'], $this->illuminant, $this->observer, $result['strictMode']);
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

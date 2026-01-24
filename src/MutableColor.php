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
use Negarity\Color\Registry\ColorSpaceRegistry;
use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEObserver;
use Negarity\Color\Exception\UnsupportedColorSpaceException;
use Negarity\Color\Exception\ColorSpaceNotFoundException;
use Negarity\Color\Exception\InvalidColorValueException;

final class MutableColor extends AbstractColor
{
    /**
     * Constructor.
     * 
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @param array<string, float|int> $values
     * @param CIEIlluminant|null $illuminant CIE standard illuminant (default: D65)
     * @param CIEObserver|null $observer CIE standard observer (default: TwoDegree)
     * @return void
     * @throws InvalidColorValueException
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

    /**
     * Set a specific color channel by name.
     * 
     * @param string $name
     * @param float|int $value
     * @return void
     * @throws InvalidColorValueException
     */
    public function setChannel(string $name, float|int $value)
    {
        if (!in_array($name, $this->getChannels(), true)) {
            throw new InvalidColorValueException("Channel '{$name}' does not exist in color space '{$this->getColorSpaceName()}'.");
        }
        
        $type = gettype($value);
        if ($type !== 'integer' && $type !== 'double' && $type !== 'float') {
            throw new InvalidColorValueException("Channel '{$name}' must be of type int or float. It's type is '{$type}'.");
        }
        
        // Convert to float and always store original value (never clamp in storage)
        // Type validation done above (int/float check)
        // Range validation: In strict mode we clamp on-the-fly, in non-strict we allow out-of-range
        $floatValue = (float)$value;
        $this->values[$name] = $floatValue;
    }

    /**
     * Set the CIE standard illuminant for this color.
     * 
     * @param CIEIlluminant $illuminant The new illuminant
     * @return $this
     */
    public function setIlluminant(CIEIlluminant $illuminant): static
    {
        $this->illuminant = $illuminant;
        return $this;
    }

    /**
     * Set the CIE standard observer for this color.
     * 
     * @param CIEObserver $observer The new observer
     * @return $this
     */
    public function setObserver(CIEObserver $observer): static
    {
        $this->observer = $observer;
        return $this;
    }

    #[\Override]
    public function without(array $channels): static
    {
        foreach ($channels as $channel) {
            if (!in_array($channel, $this->getChannels(), true)) {
                throw new InvalidColorValueException("Channel '{$channel}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
            $this->values[$channel] = $this->colorSpace::getChannelDefaultValue($channel);
        }

        return $this;
    }

    #[\Override]
    public function with(array $channels): static
    {
        foreach ($channels as $channel => $value) {
            if (!in_array($channel, $this->getChannels(), true)) {
                throw new InvalidColorValueException("Channel '{$channel}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
            $type = gettype($value);
            if ($type !== 'integer' && $type !== 'double' && $type !== 'float') {
                throw new InvalidColorValueException("Channel '{$channel}' must be of type int or float.");
            }
            // Convert to float and always store original value (never clamp in storage)
            $floatValue = (float)$value;
            $this->values[$channel] = $floatValue;
        }

        return $this;
    }

    #[\Override]
    public function toRGB(): static
    {
        $rgbValues = $this->convertToColorSpace('rgb');
        $this->colorSpace = RGB::class;
        $this->values = $rgbValues;
        return $this;
    }

    #[\Override]
    public function toRGBA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new InvalidColorValueException('Alpha value must be between 0 and 255');
        }

        $r = $g = $b = 0;

        switch ($this->colorSpace) {
            case RGBA::class:
                $r = $this->getR();
                $g = $this->getG();
                $b = $this->getB();
                $alpha = $this->getA();
                break;
            case RGB::class:
                $r = $rgb->getR();
                $g = $rgb->getG();
                $b = $rgb->getB();
                break;
            case HSLA::class:
            default:
                $rgb = $hsla->toRGB();
                $r = $rgb->getR();
                $g = $rgb->getG();
                $b = $rgb->getB();
                $alpha = $hsla->getA();
        }

        $this->colorSpace = RGBA::class;
        $this->values = [];
        $this->values['r'] = max(0, min(255, (int)round($r)));
        $this->values['g'] = max(0, min(255, (int)round($g)));
        $this->values['b'] = max(0, min(255, (int)round($b)));
        $this->values['a'] = $alpha;

        return $this;
    }

    #[\Override]
    public function toCMYK(): static
    {
        $cmykValues = $this->convertToColorSpace('cmyk');
        try {
            $cmykClass = ColorSpaceRegistry::get('cmyk');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "CMYK color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        $this->colorSpace = $cmykClass;
        $this->values = $cmykValues;
        return $this;
    }

    #[\Override]
    public function toHSL(): static
    {
        $hslValues = $this->convertToColorSpace('hsl');
        try {
            $hslClass = ColorSpaceRegistry::get('hsl');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "HSL color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        $this->colorSpace = $hslClass;
        $this->values = $hslValues;
        return $this;
    }

    #[\Override]
    public function toHSLA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new InvalidColorValueException('Alpha value must be between 0 and 255');
        }

        // If already HSLA, update alpha if different
        if ($this->colorSpace === HSLA::class) {
            if ($this->getA() !== $alpha) {
                $this->values['a'] = $alpha;
            }
            return $this;
        }

        // If HSL, convert to HSLA with specified alpha
        if ($this->colorSpace === HSL::class) {
            $this->values['a'] = $alpha;
            try {
                $hslaClass = ColorSpaceRegistry::get('hsla');
            } catch (ColorSpaceNotFoundException $e) {
                throw new ColorSpaceNotFoundException(
                    "HSLA color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                    0,
                    $e
                );
            }
            $this->colorSpace = $hslaClass;
            return $this;
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
        $this->colorSpace = $hslaClass;
        $this->values = $hslaValues;
        return $this;
    }

    #[\Override]
    public function toHSV(): static
    {
        $hsvValues = $this->convertToColorSpace('hsv');
        try {
            $hsvClass = ColorSpaceRegistry::get('hsv');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "HSV color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        $this->colorSpace = $hsvClass;
        $this->values = $hsvValues;
        return $this;
    }

    #[\Override]
    public function toLab(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $labValues = $this->convertToColorSpace('lab', $illuminant, $observer);
        try {
            $labClass = ColorSpaceRegistry::get('lab');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "Lab color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        $this->colorSpace = $labClass;
        $this->values = $labValues;
        $this->illuminant = $illuminant;
        $this->observer = $observer;
        return $this;
    }

    #[\Override]
    public function toLCh(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $lchValues = $this->convertToColorSpace('lch', $illuminant, $observer);
        try {
            $lchClass = ColorSpaceRegistry::get('lch');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "LCh color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        $this->colorSpace = $lchClass;
        $this->values = $lchValues;
        $this->illuminant = $illuminant;
        $this->observer = $observer;
        return $this;
    }

    #[\Override]
    public function toXYZ(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $xyzValues = $this->convertToColorSpace('xyz', $illuminant, $observer);
        try {
            $xyzClass = ColorSpaceRegistry::get('xyz');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "XYZ color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        $this->colorSpace = $xyzClass;
        $this->values = $xyzValues;
        $this->illuminant = $illuminant;
        $this->observer = $observer;
        return $this;
    }

    #[\Override]
    public function toYCbCr(): static
    {
        $ycbcrValues = $this->convertToColorSpace('ycbcr');
        try {
            $ycbcrClass = ColorSpaceRegistry::get('ycbcr');
        } catch (ColorSpaceNotFoundException $e) {
            throw new ColorSpaceNotFoundException(
                "YCbCr color space not registered. Call ColorSpaceRegistry::registerBuiltIn() first.",
                0,
                $e
            );
        }
        $this->colorSpace = $ycbcrClass;
        $this->values = $ycbcrValues;
        return $this;
    }

    #[\Override]
    public function withIlluminant(CIEIlluminant $illuminant): static
    {
        if (!$this->colorSpace::supportsIlluminant()) {
            throw new UnsupportedColorSpaceException(
                "Color space '{$this->getColorSpaceName()}' does not support illuminants."
            );
        }
        $this->illuminant = $illuminant;
        return $this;
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

        // Preserve original color space
        $originalSpaceClass = $this->colorSpace;
        
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
        $rgbValues = \Negarity\Color\ColorSpace\XYZ::toRGB($adaptedXyz, $targetIlluminant, $this->observer);
        $adaptedValues = $originalSpaceClass::fromRGB($rgbValues, 255, $targetIlluminant, $this->observer);

        $this->colorSpace = $originalSpaceClass;
        $this->values = $adaptedValues;
        $this->illuminant = $targetIlluminant;
        return $this;
    }

    #[\Override]
    public function adaptObserver(CIEObserver $targetObserver): static
    {
        if (!$this->colorSpace::supportsObserver()) {
            throw new UnsupportedColorSpaceException(
                "Color space '{$this->getColorSpaceName()}' does not support observers."
            );
        }

        // Preserve original color space
        $originalSpaceClass = $this->colorSpace;
        
        // Convert to RGB (observer-independent intermediate space)
        $rgb = $this->toRGB();
        $rgbValues = ['r' => $rgb->getR(), 'g' => $rgb->getG(), 'b' => $rgb->getB()];

        // Convert RGB back to original color space with new observer
        // This will use the new observer's reference white for Lab/XYZ conversions
        $adaptedValues = $originalSpaceClass::fromRGB($rgbValues, 255, $this->illuminant, $targetObserver);

        $this->colorSpace = $originalSpaceClass;
        $this->values = $adaptedValues;
        $this->observer = $targetObserver;
        return $this;
    }
}

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
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $colorSpace, 
        array $values = [],
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ) {
        parent::__construct($colorSpace, $values, $illuminant, $observer);
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
        if (in_array($name, $this->getChannels(), true)) {
            $this->values[$name] = $value;
        } else {
            throw new InvalidColorValueException("Channel '{$name}' does not exist in color space '{$this->getName()}'.");
        }
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
                throw new \InvalidArgumentException("Channel '{$channel}' does not exist in color space '{$this->getColorSpaceName()}'.");
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
                throw new \InvalidArgumentException("Channel '{$channel}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
            $type = gettype($value);
            if ($type !== 'integer' && $type !== 'double' && $type !== 'float') {
                throw new \InvalidArgumentException("Channel '{$channel}' must be of type int or float.");
            }
            // Convert to float
            $floatValue = (float)$value;
            
            // In strict mode: clamp immediately and store original
            // In non-strict mode: validate (throws if out of range) and store original
            if (static::STRICT_CLAMPING) {
                $this->originalValues[$channel] = $floatValue;
                $this->values[$channel] = $this->colorSpace::clampValue($channel, $floatValue);
            } else {
                $this->colorSpace::validateValue($channel, $floatValue);
                $this->values[$channel] = $floatValue;
            }
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
            throw new \InvalidArgumentException('Alpha value must be between 0 and 255');
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
        $cmykClass = ColorSpaceRegistry::get('cmyk');
        $this->colorSpace = $cmykClass;
        $this->values = $cmykValues;
        return $this;
    }

    #[\Override]
    public function toHSL(): static
    {
        $hslValues = $this->convertToColorSpace('hsl');
        $hslClass = ColorSpaceRegistry::get('hsl');
        $this->colorSpace = $hslClass;
        $this->values = $hslValues;
        return $this;
    }

    #[\Override]
    public function toHSLA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new \InvalidArgumentException('Alpha value must be between 0 and 255');
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
            $hslaClass = ColorSpaceRegistry::get('hsla');
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
        $hslaClass = ColorSpaceRegistry::get('hsla');
        $this->colorSpace = $hslaClass;
        $this->values = $hslaValues;
        return $this;
    }

    #[\Override]
    public function toHSV(): static
    {
        $hsvValues = $this->convertToColorSpace('hsv');
        $hsvClass = ColorSpaceRegistry::get('hsv');
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
        $labClass = ColorSpaceRegistry::get('lab');
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
        $lchClass = ColorSpaceRegistry::get('lch');
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
        $xyzClass = ColorSpaceRegistry::get('xyz');
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
        $ycbcrClass = ColorSpaceRegistry::get('ycbcr');
        $this->colorSpace = $ycbcrClass;
        $this->values = $ycbcrValues;
        return $this;
    }
}

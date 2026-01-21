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
            if (gettype($value) !== 'integer' && gettype($value) !== 'float') {
                throw new \InvalidArgumentException("Channel '{$channel}' must be of type int or float.");
            }
            $this->values[$channel] = $value;
        }

        return $this;
    }

    #[\Override]
    public function toRGB(): static
    {
        $r = $g = $b = 0;

        switch ($this->colorSpace) {
            case RGB::class:
                $r = $this->getR();
                $g = $this->getG();
                $b = $this->getB();
                break;
            case RGBA::class:
                $r = $this->getR();
                $g = $this->getG();
                $b = $this->getB();
                break;
            case CMYK::class:
                $rgb = $this->convertCmykToRgb();
                $r = $rgb['r'];
                $g = $rgb['g'];
                $b = $rgb['b'];
                break;
            case HSL::class:
                $rgb = $this->convertHslToRgb();
                $r = $rgb['r'];
                $g = $rgb['g'];
                $b = $rgb['b'];
                break;
            case HSLA::class:
                $rgb = $this->convertHslaToRgb();
                $r = $rgb['r'];
                $g = $rgb['g'];
                $b = $rgb['b'];
                break;
            case HSV::class:
                $rgb = $this->convertHsvToRgb();
                $r = $rgb['r'];
                $g = $rgb['g'];
                $b = $rgb['b'];
                break;
            case Lab::class:
                $rgb = $this->convertLabToRgb();
                $r = $rgb['r'];
                $g = $rgb['g'];
                $b = $rgb['b'];
                break;
            case LCh::class:
                $rgb = $this->convertLchToRgb();
                $r = $rgb['r'];
                $g = $rgb['g'];
                $b = $rgb['b'];
                break;
            case XYZ::class:
                $rgb = $this->convertXyzToRgb();
                $r = $rgb['r'];
                $g = $rgb['g'];
                $b = $rgb['b'];
                break;
            case YCbCr::class:
                $rgb = $this->convertYcbcrToRgb();
                $r = $rgb['r'];
                $g = $rgb['g'];
                $b = $rgb['b'];
                break;
            default:
                throw new \RuntimeException('Conversion to RGB not implemented for this color space.');
        }

        $this->colorSpace = RGB::class;
        $this->values = [];
        $this->values['r'] = max(0, min(255, (int)round($r)));
        $this->values['g'] = max(0, min(255, (int)round($g)));
        $this->values['b'] = max(0, min(255, (int)round($b)));

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
        $rgb = $this->toRGB();
        $cmyk = $this->convertRgbToCmyk($rgb->getR(), $rgb->getG(), $rgb->getB());
        $this->colorSpace = CMYK::class;
        $this->values = $cmyk;
        return $this;
    }

    #[\Override]
    public function toHSL(): static
    {
        $rgb = $this->toRGB();
        $hsl = $this->convertRgbToHsl($rgb->getR(), $rgb->getG(), $rgb->getB());
        $this->colorSpace = HSL::class;
        $this->values = $hsl;
        return $this;
    }

    #[\Override]
    public function toHSLA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new \InvalidArgumentException('Alpha value must be between 0 and 255');
        }

        $h = $s = $l = 0;

        switch ($this->colorSpace) {
            case HSLA::class:
                $h = $this->getH();
                $s = $this->getS();
                $l = $this->getL();
                $alpha = $this->getA();
                break;
            case HSL::class:
                $h = $hsl->getH();
                $s = $hsl->getS();
                $l = $hsl->getL();
                break;
            case RGBA::class:
                $rgbColor = self::rgb($this->getR(), $this->getG(), $this->getB());
                $hslColor = $rgbColor->toHSL();
                $h = $hsl->getH();
                $s = $hsl->getS();
                $l = $hsl->getL();
                $alpha = $this->getA();
                break;
            default:
                $hsl = $this->toHSL();
                $h = $hsl->getH();
                $s = $hsl->getS();
                $l = $hsl->getL();
        }

        $this->colorSpace = HSLA::class;
        $this->values = [];
        $this->values['h'] = (int)round($h);
        $this->values['s'] = (int)round($s);
        $this->values['l'] = (int)round($l);
        $this->values['a'] = $alpha;

        return $this;
    }

    #[\Override]
    public function toHSV(): static
    {
        $rgb = $this->toRGB();
        $hsv = $this->convertRgbToHsv($rgb->getR(), $rgb->getG(), $rgb->getB());
        $this->colorSpace = HSV::class;
        $this->values = $hsv;
        return $this;
    }

    #[\Override]
    public function toLab(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $rgb = $this->toRGB();
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $lab = $this->convertRgbToLab($rgb->getR(), $rgb->getG(), $rgb->getB(), $illuminant, $observer);
        $this->colorSpace = Lab::class;
        $this->values = ['l' => $lab['l'], 'a' => $lab['a'], 'b' => $lab['b']];
        $this->illuminant = $illuminant;
        $this->observer = $observer;
        return $this;
    }

    #[\Override]
    public function toLCh(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $rgb = $this->toRGB();
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $lch = $this->convertRgbToLch($rgb->getR(), $rgb->getG(), $rgb->getB(), $illuminant, $observer);
        $this->colorSpace = LCh::class;
        $this->values = ['l' => $lch['l'], 'c' => $lch['c'], 'h' => $lch['h']];
        $this->illuminant = $illuminant;
        $this->observer = $observer;
        return $this;
    }

    #[\Override]
    public function toXYZ(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $rgb = $this->toRGB();
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $xyz = $this->convertRgbToXyz($rgb->getR(), $rgb->getG(), $rgb->getB());
        $this->colorSpace = XYZ::class;
        $this->values = ['x' => $xyz['x'], 'y' => $xyz['y'], 'z' => $xyz['z']];
        $this->illuminant = $illuminant;
        $this->observer = $observer;
        return $this;
    }

    #[\Override]
    public function toYCbCr(): static
    {
        $rgb = $this->toRGB();
        $ycbcr = $this->convertRgbToYcbcr($rgb->getR(), $rgb->getG(), $rgb->getB());
        $this->colorSpace = YCbCr::class;
        $this->values = $ycbcr;
        return $this;
    }
}

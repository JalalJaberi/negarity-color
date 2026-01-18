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

final class Color extends AbstractColor
{
    /**
     * Create a new Color instance.
     * 
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @param array<string, float|int> $values
     * @return void
     * @throws \InvalidArgumentException
     */
    public function __construct(string $colorSpace, array $values = [])
    {
        parent::__construct($colorSpace, $values);
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

        return new self($this->colorSpace, $values);
    }

    #[\Override]
    public function with(array $channels): static
    {
        $values = $this->values;
        foreach ($channels as $channel => $value) {
            if (!in_array($channel, $this->getChannels(), true)) {
                throw new \InvalidArgumentException("Channel '{$channel}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
            if (gettype($value) !== 'integer' && gettype($value) !== 'float') {
                throw new \InvalidArgumentException("Channel '{$channel}' must be of type int or float.");
            }
            $values[$channel] = $value;
        }

        return new self($this->colorSpace, $values);
    }

    #[\Override]
    public function toRGB(): static
    {
        switch ($this->colorSpace) {
            case RGB::class:
                return new self($this->colorSpace, $this->values);
            case RGBA::class:
                /** @var RGBA $rgba */
                $rgba = $this->colorSpace;
                return self::rgb($rgba->getR(), $rgba->getG(), $rgba->getB());
            case CMYK::class:
                $rgb = $this->convertCmykToRgb();
                return self::rgb($rgb['r'], $rgb['g'], $rgb['b']);
            case HSL::class:
                $rgb = $this->convertHslToRgb();
                return self::rgb($rgb['r'], $rgb['g'], $rgb['b']);
            case HSLA::class:
                $rgb = $this->convertHslaToRgb();
                return self::rgb($rgb['r'], $rgb['g'], $rgb['b']);
            case HSV::class:
                $rgb = $this->convertHsvToRgb();
                return self::rgb($rgb['r'], $rgb['g'], $rgb['b']);
            case Lab::class:
                $rgb = $this->convertLabToRgb();
                return self::rgb($rgb['r'], $rgb['g'], $rgb['b']);
            case LCh::class:
                $rgb = $this->convertLchToRgb();
                return self::rgb($rgb['r'], $rgb['g'], $rgb['b']);
            case XYZ::class:
                $rgb = $this->convertXyzToRgb();
                return self::rgb($rgb['r'], $rgb['g'], $rgb['b']);
            case YCbCr::class:
                $rgb = $this->convertYcbcrToRgb();
                return self::rgb($rgb['r'], $rgb['g'], $rgb['b']);
            default:
                throw new \RuntimeException('Conversion to RGB not implemented for this color space.');
        }
    }

    #[\Override]
    public function toRGBA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new \InvalidArgumentException('Alpha value must be between 0 and 255');
        }

        switch ($this->colorSpace) {
            case RGBA::class:
                return new self($this->colorSpace, $this->values);
            case RGB::class:
                return self::rgba($this->getR(), $this->getG(), $this->getB(), $alpha);
            case HSLA::class:
                $rgb = $this->toRGB();
                return self::rgba($rgb->getR(), $rgb->getG(), $rgb->getB(), $hsla->getA());
            default:
                $rgb = $this->toRGB();
                return self::rgba($rgb->getR(), $rgb->getG(), $rgb->getB(), $alpha);
        }
    }

    #[\Override]
    public function toCMYK(): static
    {
        $rgb = $this->toRGB();
        $cmyk = $this->convertRgbToCmyk($rgb->getR(), $rgb->getG(), $rgb->getB());
        return self::cmyk($cmyk['c'], $cmyk['m'], $cmyk['y'], $cmyk['k']);
    }

    #[\Override]
    public function toHSL(): static
    {
        $rgb = $this->toRGB();
        $hsl = $this->convertRgbToHsl($rgb->getR(), $rgb->getG(), $rgb->getB());
        return self::hsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    #[\Override]
    public function toHSLA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new \InvalidArgumentException('Alpha value must be between 0 and 255');
        }

        switch ($this->colorSpace) {
            case HSLA::class:
                return new self($this->colorSpace, $this->values);
            case HSL::class:
                return self::hsla(
                    $this->getH(),
                    $this->getS(),
                    $this->getL(),
                    $alpha
                );
            case RGBA::class:
                $rgb = self::rgb($this->getR(), $this->getG(), $this->getB());
                $hsl = $rgb->toHSL();
                return self::hsla(
                    $hsl->getH(),
                    $hsl->getS(),
                    $hsl->getL(),
                    $this->getA()
                );
            default:
                /** @var HSL $hsl */
                $hsl = $this->toHSL();
                return self::hsla(
                    $hsl->getH(),
                    $hsl->getS(),
                    $hsl->getL(),
                    $alpha
                );
        }
    }

    #[\Override]
    public function toHSV(): static
    {
        $rgb = $this->toRGB();
        $hsv = $this->convertRgbToHsv($rgb->getR(), $rgb->getG(), $rgb->getB());
        return self::hsv($hsv['h'], $hsv['s'], $hsv['v']);
    }

    #[\Override]
    public function toLab(): static
    {
        $rgb = $this->toRGB();
        $lab = $this->convertRgbToLab($rgb->getR(), $rgb->getG(), $rgb->getB());
        return self::lab($lab['l'], $lab['a'], $lab['b']);
    }

    #[\Override]
    public function toLCh(): static
    {
        $rgb = $this->toRGB();
        $lch = $this->convertRgbToLch($rgb->getR(), $rgb->getG(), $rgb->getB());
        return self::lch($lch['l'], $lch['c'], $lch['h']);
    }

    #[\Override]
    public function toXYZ(): static
    {
        $rgb = $this->toRGB();
        $xyz = $this->convertRgbToXyz($rgb->getR(), $rgb->getG(), $rgb->getB());
        return self::xyz($xyz['x'], $xyz['y'], $xyz['z']);
    }

    #[\Override]
    public function toYCbCr(): static
    {
        $rgb = $this->toRGB();
        $ycbcr = $this->convertRgbToYcbcr($rgb->getR(), $rgb->getG(), $rgb->getB());
        return self::ycbcr($ycbcr['y'], $ycbcr['cb'], $ycbcr['cr']);
    }
}

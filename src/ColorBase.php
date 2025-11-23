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

abstract class ColorBase
{
    /** @var NamedColorRegistryInterface[] */
    private static array $registries = [];

    public function __construct(
        protected readonly ColorSpaceInterface $colorSpace
    ) {
    }

    public static function addRegistry(NamedColorRegistryInterface $registry): void
    {
        static::$registries[] = $registry;
    }

    public function getColorSpace(): ColorSpaceInterface
    {
        return $this->colorSpace;
    }

    public function getColorSpaceName(): string
    {
        return $this->colorSpace->getName();
    }

    public function getChannels(): array
    {
        return $this->colorSpace->getChannels();
    }

    public function getChannel(string $name): int
    {
        return $this->colorSpace->getChannel($name);
    }

    public function toArray(): array
    {
        return $this->colorSpace->toArray();
    }

    public function without(array $channels): static
    {
        return new static($this->colorSpace->without($channels));
    }

    public function with(array $channels): static
    {
        return new static($this->colorSpace->with($channels));
    }

    public function __toString(): string
    {
        return $this->getColorSpaceName() . '(' . implode(', ', $this->toArray()) . ')';
    }

    public static function rgb(int $r, int $g, int $b): static
    {
        return new static(new RGB($r, $g, $b));
    }

    public static function rgba(int $r, int $g, int $b, float $a): static
    {
        return new static(new RGBA($r, $g, $b, $a));
    }

    public static function cmyk(int $c, int $m, int $y, int $k): static
    {
        return new static(new CMYK($c, $m, $y, $k));
    }

    public static function hsl(int $h, int $s, int $l): static
    {
        return new static(new HSL($h, $s, $l));
    }

    public static function hsla(int $h, int $s, int $l, float $a): static
    {
        return new static(new HSLA($h, $s, $l, $a));
    }

    public static function hsv(int $h, int $s, int $v): static
    {
        return new static(new HSV($h, $s, $v));
    }

    public static function lab(float $l, float $a, float $b): static
    {
        return new static(new Lab($l, $a, $b));
    }

    public static function lch(float $l, float $c, float $h): static
    {
        return new static(new LCh($l, $c, $h));
    }

    public static function xyz(float $x, float $y, float $z): static
    {
        return new static(new XYZ($x, $y, $z));
    }

    public static function ycbcr(int $y, int $cb, int $cr): static
    {
        return new static(new YCbCr($y, $cb, $cr));
    }

    public static function hex(string $value, string $colorSpaceName = 'rgb'): static
    {
        $value = ltrim($value, '#');
        $r = $g = $b = $a = '';

        if (strlen($value) === 8) {
            $r = hexdec(substr($value, 0, 2));
            $g = hexdec(substr($value, 2, 2));
            $b = hexdec(substr($value, 4, 2));
            $a = hexdec(substr($value, 6, 2));
        } else if (strlen($value) === 6) {
            $r = hexdec(substr($value, 0, 2));
            $g = hexdec(substr($value, 2, 2));
            $b = hexdec(substr($value, 4, 2));
        } else if (strlen($value) === 4) {
            $r = hexdec(str_repeat(substr($value, 0, 1), 2));
            $g = hexdec(str_repeat(substr($value, 1, 1), 2));
            $b = hexdec(str_repeat(substr($value, 2, 1), 2));
            $a = hexdec(str_repeat(substr($value, 3, 1), 2));
        } else if (strlen($value) === 3) {
            $r = hexdec(str_repeat(substr($value, 0, 1), 2));
            $g = hexdec(str_repeat(substr($value, 1, 1), 2));
            $b = hexdec(str_repeat(substr($value, 2, 1), 2));
        } else {
            throw new \InvalidArgumentException('Hex value must be 3 (rgb), 4 (rgba), 6 (rrggbb), or 8 (rrggbbaa) characters long.');
        }
        return match (strtolower($colorSpaceName)) {
            'rgb' => static::rgb($r, $g, $b),
            'rgba' => static::rgba($r, $g, $b, 255),
            'cmyk' => static::rgb($r, $g, $b)->toCMYK(),
            'hsl' => static::rgb($r, $g, $b)->toHSL(),
            'hsla' => static::rgb($r, $g, $b)->toHSLA(255),
            'hsv' => static::rgb($r, $g, $b)->toHSV(),
            'lab' => static::rgb($r, $g, $b)->toLab(),
            'lch' => static::rgb($r, $g, $b)->toLCh(),
            'xyz' => static::rgb($r, $g, $b)->toXYZ(),
            'ycbcr' => static::rgb($r, $g, $b)->toYCbCr(),
            default => throw new \InvalidArgumentException('Unsupported color space for hex input.'),
        };
    }

    public static function __callStatic(string $name, array $arguments): static
    {
        $colorName = strtolower($name);
        $targetSpace = $arguments[0] ?? 'rgb';

        foreach (static::$registries as $registry) {
            if ($registry->has($colorName, $targetSpace)) {

                // registry gives back a ColorSpaceInterface
                $space = $registry->getColorByName($colorName, $targetSpace);

                // you just wrap that into a Color object
                return new static($space);
            }
        }

        throw new \InvalidArgumentException(
            "Named color '{$colorName}' not found in space '{$targetSpace}'"
        );
    }

    public abstract function toRGB(): static;
    public abstract function toRGBA(int $alpha = 255): static;
    public abstract function toCMYK(): static;
    public abstract function toHSL(): static;
    public abstract function toHSLA(int $alpha = 255): static;
    public abstract function toHSV(): static;
    public abstract function toLab(): static;
    public abstract function toLCh(): static;
    public abstract function toXYZ(): static;
    public abstract function toYCbCr(): static;

    public function toHex(): string
    {
        if (get_class($this->colorSpace) === RGB::class) {
            /** @var RGB $rgbSpace */
            $rgbSpace = $this->colorSpace;
            return sprintf(
                '#%02X%02X%02X',
                $rgbSpace->getChannel('r'),
                $rgbSpace->getChannel('g'),
                $rgbSpace->getChannel('b')
            );
        } else if (get_class($this->colorSpace) === RGBA::class) {
            /** @var RGBA $rgbaSpace */
            $rgbaSpace = $this->colorSpace;
            return sprintf(
                '#%02X%02X%02X',
                $rgbaSpace->getChannel('r'),
                $rgbaSpace->getChannel('g'),
                $rgbaSpace->getChannel('b')
            );
        } else {
            return $this->toRGB()->toHex();
        }
    }
}

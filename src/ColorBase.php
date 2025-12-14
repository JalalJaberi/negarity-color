<?php

declare(strict_types=1);

namespace Negarity\Color;

use Negarity\Color\ColorSpace\{
    ColorSpaceEnum,
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
use Negarity\Color\Filter\{
    FilterRegistry,
    Unary\UnaryColorFilterInterface,
    Parameterized\ParameterizedColorFilterInterface,
    Binary\BinaryColorFilterInterface
};

abstract class ColorBase implements \JsonSerializable
{
    /** @var NamedColorRegistryInterface[] */
    private static array $registries = [];
    /** @var ColorSpaceInterface */
    protected readonly ColorSpaceInterface $colorSpace;

    public function __construct(ColorSpaceInterface $colorSpace, $channels = [])
    {
        // @TODO: enable once mutability is supported
        /*$colorSpaceChannels = $colorSpace->getChannels();
        if (count($channels) != count($colorSpaceChannels)) {
            throw new \InvalidArgumentException('Channel count does not match color space requirements.');
        }
        foreach ($channels as $value) {
            $this->colorSpace->setChannel($colorSpaceChannels[$i], $value);
        }*/

        $colorSpaceChannels = $colorSpace->getChannels();
        if (count($channels) > 0) {
            if (count($channels) != count($colorSpaceChannels)) {
                throw new \InvalidArgumentException('Channel count does not match color space requirements.');
            }
            // @Note: the $channels array is expected to have a specific structure,
            // it's not handled to throw errors if the structure doesn't match.
            $this->colorSpace = $colorSpace->with(array_combine($colorSpaceChannels, $channels));
        } else {
            $this->colorSpace = $colorSpace;
        }
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

    public function getChannel(string $name): float|int
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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function rgb(int $r, int $g, int $b): static
    {
        return new static(new RGB($r, $g, $b));
    }

    public static function rgba(int $r, int $g, int $b, int $a): static
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

    public static function hsla(int $h, int $s, int $l, int $a): static
    {
        return new static(new HSLA($h, $s, $l, $a));
    }

    public static function hsv(int $h, int $s, int $v): static
    {
        return new static(new HSV($h, $s, $v));
    }

    public static function lab(int $l, int $a, int $b): static
    {
        return new static(new Lab($l, $a, $b));
    }

    public static function lch(int $l, int $c, int $h): static
    {
        return new static(new LCh($l, $c, $h));
    }

    public static function xyz(int $x, int $y, int $z): static
    {
        return new static(new XYZ($x, $y, $z));
    }

    public static function ycbcr(int $y, int $cb, int $cr): static
    {
        return new static(new YCbCr($y, $cb, $cr));
    }

    public static function hex(string $value, string $colorSpaceName = ColorSpaceEnum::RGB->value): static
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
            ColorSpaceEnum::RGB->value => static::rgb($r, $g, $b),
            ColorSpaceEnum::RGBA->value => static::rgba($r, $g, $b, 255),
            ColorSpaceEnum::CMYK->value => static::rgb($r, $g, $b)->toCMYK(),
            ColorSpaceEnum::HSL->value => static::rgb($r, $g, $b)->toHSL(),
            ColorSpaceEnum::HSLA->value => static::rgb($r, $g, $b)->toHSLA(255),
            ColorSpaceEnum::HSV->value => static::rgb($r, $g, $b)->toHSV(),
            ColorSpaceEnum::LAB->value => static::rgb($r, $g, $b)->toLab(),
            ColorSpaceEnum::LCH->value => static::rgb($r, $g, $b)->toLCh(),
            ColorSpaceEnum::XYZ->value => static::rgb($r, $g, $b)->toXYZ(),
            ColorSpaceEnum::YCBCR->value => static::rgb($r, $g, $b)->toYCbCr(),
            default => throw new \InvalidArgumentException('Unsupported color space for hex input.'),
        };
    }

    public static function __callStatic(string $name, array $arguments): static
    {
        $colorName = strtolower($name);
        $targetSpace = $arguments[0] ?? ColorSpaceEnum::RGB->value;

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
                '#%02X%02X%02X%02X',
                $rgbaSpace->getChannel('r'),
                $rgbaSpace->getChannel('g'),
                $rgbaSpace->getChannel('b'),
                $rgbaSpace->getChannel('a')
            );
        } else {
            return $this->toRGB()->toHex();
        }
    }

    public function __call(string $name, array $args)
    {
        if (!FilterRegistry::has($name)) {
            throw new \BadMethodCallException("Filter '{$name}' not found.");
        }
    
        $filter = FilterRegistry::get($name);
    
        // Unary
        if ($filter instanceof UnaryColorFilterInterface) {
            return $filter->apply($this);
        }
    
        // Parameterized
        if ($filter instanceof ParameterizedColorFilterInterface) {
            $value = $args[0] ?? null;
            return $filter->apply($this, $value);
        }
    
        // Binary: $color->blend($otherColor)
        if ($filter instanceof BinaryColorFilterInterface) {
            $other = $args[0] ?? null;
            return $filter->apply($this, $other);
        }
    
        throw new \RuntimeException("Filter '{$name}' cannot be used with this signature.");
    }
}

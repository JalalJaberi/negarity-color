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

abstract class AbstractColor implements \JsonSerializable, ColorInterface
{
    /** @var NamedColorRegistryInterface[] */
    private static array $registries = [];
    /** @var class-string<ColorSpaceInterface> */
    protected string $colorSpace;
    protected array $values = [];

    /**
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @param array<string, float|int> $values
     */
    public function __construct(string $colorSpace, $values = [])
    {
        if (!is_subclass_of($colorSpace, ColorSpaceInterface::class)) {
            throw new \InvalidArgumentException(
                "$colorSpace must implement ColorSpaceInterface"
            );
        }

        $this->colorSpace = $colorSpace;

        $colorSpaceChannels = $colorSpace::getChannels();

        foreach ($colorSpaceChannels as $name) {
            if (!isset($values[$name])) {
                $this->values[$name] = $colorSpace::getChannelDefaultValue($name);
            } else if (gettype($values[$name]) !== 'integer' && gettype($values[$name]) !== 'float') {
                throw new \InvalidArgumentException("Channel '{$name}' must be of type int or float.");
            } else {
                $this->values[$name] = $values[$name];
            }
        }
    }

    public static function addRegistry(NamedColorRegistryInterface $registry): void
    {
        static::$registries[] = $registry;
    }

    /** @return class-string<ColorSpaceInterface> */
    public function getColorSpace(): string
    {
        return $this->colorSpace;
    }

    public function getColorSpaceName(): string
    {
        return $this->colorSpace::getName();
    }

    public function getChannels(): array
    {
        return $this->colorSpace::getChannels();
    }

    public function getChannel(string $name): float|int
    {
        if (!in_array($name, $this->getChannels(), true)) {
            throw new \InvalidArgumentException("Channel '{$name}' does not exist in color space '{$this->getColorSpaceName()}'.");
        }
        return $this->values[$name];
    }

    public function toArray(): array
    {
        return [
            'color-space' => $this->getColorSpaceName(),
            'values' => $this->values,
        ];
    }

    public function __toString(): string
    {
        return $this->getColorSpaceName() . '(' . implode(', ', array_values($this->values)) . ')';
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function rgb(int $r, int $g, int $b): static
    {
        return new static(RGB::class, ['r' => $r, 'g' => $g, 'b' => $b]);
    }

    public static function rgba(int $r, int $g, int $b, int $a): static
    {
        return new static(RGBA::class, ['r' => $r, 'g' => $g, 'b' => $b, 'a' => $a]);
    }

    public static function cmyk(int $c, int $m, int $y, int $k): static
    {
        return new static(CMYK::class, ['c' => $c, 'm' => $m, 'y' => $y, 'k' => $k]);
    }

    public static function hsl(int $h, int $s, int $l): static
    {
        return new static(HSL::class, ['h' => $h, 's' => $s, 'l' => $l]);
    }

    public static function hsla(int $h, int $s, int $l, int $a): static
    {
        return new static(HSLA::class, ['h' => $h, 's' => $s, 'l' => $l, 'a' => $a]);
    }

    public static function hsv(int $h, int $s, int $v): static
    {
        return new static(HSV::class, ['h' => $h, 's' => $s, 'v' => $v]);
    }

    public static function lab(int $l, int $a, int $b): static
    {
        return new static(Lab::class, ['l' => $l, 'a' => $a, 'b' => $b]);
    }

    public static function lch(int $l, int $c, int $h): static
    {
        return new static(LCh::class, ['l' => $l, 'c' => $c, 'h' => $h]);
    }

    public static function xyz(int $x, int $y, int $z): static
    {
        return new static(XYZ::class, ['x' => $x, 'y' => $y, 'z' => $z]);
    }

    public static function ycbcr(int $y, int $cb, int $cr): static
    {
        return new static(YCbCr::class, ['y' => $y, 'cb' => $cb, 'cr' => $cr]);
    }

    /**
     * Create a Color from a hex string.
     * @param string $value Hex string (e.g. "#RRGGBB" or "RRGGBBAA")
     * @param class-string<ColorSpaceInterface> $colorSpace
     */
    public static function hex(string $value, string $colorSpace = RGB::class): static
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
            $a = 255;
        } else if (strlen($value) === 4) {
            $r = hexdec(str_repeat(substr($value, 0, 1), 2));
            $g = hexdec(str_repeat(substr($value, 1, 1), 2));
            $b = hexdec(str_repeat(substr($value, 2, 1), 2));
            $a = hexdec(str_repeat(substr($value, 3, 1), 2));
        } else if (strlen($value) === 3) {
            $r = hexdec(str_repeat(substr($value, 0, 1), 2));
            $g = hexdec(str_repeat(substr($value, 1, 1), 2));
            $b = hexdec(str_repeat(substr($value, 2, 1), 2));
            $a = 255;
        } else {
            throw new \InvalidArgumentException('Hex value must be 3 (rgb), 4 (rgba), 6 (rrggbb), or 8 (rrggbbaa) characters long.');
        }
        return match ($colorSpace) {
            RGB::class => static::rgb($r, $g, $b),
            RGBA::class => static::rgba($r, $g, $b, $a),
            CMYK::class => static::rgb($r, $g, $b)->toCMYK(),
            HSL::class => static::rgb($r, $g, $b)->toHSL(),
            HSLA::class => static::rgb($r, $g, $b)->toHSLA($a),
            HSV::class => static::rgb($r, $g, $b)->toHSV(),
            LAB::class => static::rgb($r, $g, $b)->toLab(),
            LCH::class => static::rgb($r, $g, $b)->toLCh(),
            XYZ::class => static::rgb($r, $g, $b)->toXYZ(),
            YCBCR::class => static::rgb($r, $g, $b)->toYCbCr(),
            default => throw new \InvalidArgumentException('Unsupported color space for hex input.'),
        };
    }

    public static function __callStatic(string $name, array $arguments): static
    {
        $colorName = strtolower($name);
        $colorSpace = $arguments[0] ?? RGB::class;

        foreach (static::$registries as $registry) {
            if ($registry->has($colorName, $colorSpace)) {

                // registry gives back an array of channel values
                $values = $registry->getColorValuesByName($colorName, $colorSpace);

                // you just wrap that into a Color object
                return new static($colorSpace, $values);
            }
        }

        throw new \InvalidArgumentException(
            "Named color '{$colorName}' not found in space '{$colorSpace}'"
        );
    }

    // magic function for get{ChannelName}() calls
    public function __call(string $name, array $arguments): mixed
    {
        $reservedMrthods = [ 'ColorSpace', 'ColorSpaceName', 'Channels', 'Channel' ];
        // Handle get{ChannelName} calls
        if (str_starts_with($name, 'get') && !in_array(substr($name, 3), $reservedMrthods, true)) {
            $channelName = lcfirst(substr($name, 3));
            if (in_array($channelName, $this->getChannels(), true)) {
                return $this->getChannel($channelName);
            } else {
                throw new \BadMethodCallException("Channel '{$channelName}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
        }

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
            $value = $arguments[0] ?? null;
            return $filter->apply($this, $value);
        }
    
        // Binary: $color->blend($otherColor)
        if ($filter instanceof BinaryColorFilterInterface) {
            $other = $arguments[0] ?? null;
            return $filter->apply($this, $other);
        }
    
        throw new \BadMethodCallException("Method {$name} does not exist.");
    }

    public abstract function without(array $channels): static;
    public abstract function with(array $channels): static;

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
        if ($this->colorSpace === RGB::class) {
            return sprintf(
                '#%02X%02X%02X',
                $this->getChannel('r'),
                $this->getChannel('g'),
                $this->getChannel('b')
            );
        } else if ($this->colorSpace === RGBA::class) {
            return sprintf(
                '#%02X%02X%02X%02X',
                $this->getChannel('r'),
                $this->getChannel('g'),
                $this->getChannel('b'),
                $this->getChannel('a')
            );
        } else {
            return $this->toRGB()->toHex();
        }
    }
}

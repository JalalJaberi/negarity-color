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
    /** @var array<string, float|int> */
    protected array $values = [];

    /**
     * Constructor.
     * 
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @param array<string, float|int> $values
     * @throws \InvalidArgumentException
     */
    public function __construct(string $colorSpace, array $values = [])
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
            } else if (gettype($values[$name]) !== 'integer' && gettype($values[$name]) !== 'double') {
                throw new \InvalidArgumentException("Channel '{$name}' must be of type int or double. It's type is '".gettype($values[$name])."'.");
            } else {
                // validateValue throws an exception if validation fails
                $colorSpace::validateValue($name, $values[$name]);
                $this->values[$name] = $values[$name];
            }
        }
    }

    /**
     * Add a named color registry.
     * 
     * @param NamedColorRegistryInterface $registry
     * @return void
     */
    public static function addRegistry(NamedColorRegistryInterface $registry): void
    {
        static::$registries[] = $registry;
    }

    #[\Override]
    final public function getColorSpace(): string
    {
        return $this->colorSpace;
    }

    #[\Override]
    final public function getColorSpaceName(): string
    {
        return $this->colorSpace::getName();
    }

    #[\Override]
    final public function getChannels(): array
    {
        return $this->colorSpace::getChannels();
    }

    #[\Override]
    final public function getChannel(string $name): float|int
    {
        if (!in_array($name, $this->getChannels(), true)) {
            throw new \InvalidArgumentException("Channel '{$name}' does not exist in color space '{$this->getColorSpaceName()}'.");
        }
        return $this->values[$name];
    }

    #[\Override]
    final public function toArray(): array
    {
        return [
            'color-space' => $this->getColorSpaceName(),
            'values' => $this->values,
        ];
    }

    #[\Override]
    final public function __toString(): string
    {
        return $this->getColorSpaceName() . '(' . implode(', ', array_values($this->values)) . ')';
    }

    abstract public function without(array $channels): static;
    abstract public function with(array $channels): static;

    /**
     * Serialize the color to JSON.
     * 
     * @return array<string, mixed>
     *
     * @see \JsonSerializable::jsonSerialize()
     */
    final public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    final public static function rgb(int $r, int $g, int $b): static
    {
        return new static(RGB::class, ['r' => $r, 'g' => $g, 'b' => $b]);
    }

    /**
     * Create a Color in RGBA color space.
     * 
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @param int $a Alpha channel (0-255)
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function rgba(int $r, int $g, int $b, int $a): static
    {
        return new static(RGBA::class, ['r' => $r, 'g' => $g, 'b' => $b, 'a' => $a]);
    }

    /**
     * Create a Color in CMYK color space.
     * 
     * @param int $c Cyan channel (0-100)
     * @param int $m Magenta channel (0-100)
     * @param int $y Yellow channel (0-100)
     * @param int $k Black channel (0-100)
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function cmyk(int $c, int $m, int $y, int $k): static
    {
        return new static(CMYK::class, ['c' => $c, 'm' => $m, 'y' => $y, 'k' => $k]);
    }

    /**
     * Create a Color in HSL color space.
     * 
     * @param int $h Hue channel (0-360)
     * @param int $s Saturation channel (0-100)
     * @param int $l Lightness channel (0-100)
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function hsl(int $h, int $s, int $l): static
    {
        return new static(HSL::class, ['h' => $h, 's' => $s, 'l' => $l]);
    }

    /**
     * Create a Color in HSLA color space.
     * 
     * @param int $h Hue channel (0-360)
     * @param int $s Saturation channel (0-100)
     * @param int $l Lightness channel (0-100)
     * @param int $a Alpha channel (0-100)
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function hsla(int $h, int $s, int $l, int $a): static
    {
        return new static(HSLA::class, ['h' => $h, 's' => $s, 'l' => $l, 'a' => $a]);
    }

    /**
     * Create a Color in HSV color space.
     * 
     * @param int $h Hue channel (0-360)
     * @param int $s Saturation channel (0-100)
     * @param int $v Value channel (0-100)
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function hsv(int $h, int $s, int $v): static
    {
        return new static(HSV::class, ['h' => $h, 's' => $s, 'v' => $v]);
    }

    /**
     * Create a Color in Lab color space.
     * 
     * @param int $l Lightness channel (0-100)
     * @param int $a A channel (-128 to 127)
     * @param int $b B channel (-128 to 127)
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function lab(int $l, int $a, int $b): static
    {
        return new static(Lab::class, ['l' => $l, 'a' => $a, 'b' => $b]);
    }

    /**
     * Create a Color in LCh color space.
     * 
     * @param int $l Lightness channel (0-100)
     * @param int $c Chroma channel (0-100)
     * @param int $h Hue channel (0-360)
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function lch(int $l, int $c, int $h): static
    {
        return new static(LCh::class, ['l' => $l, 'c' => $c, 'h' => $h]);
    }

    /**
     * Create a Color in XYZ color space.
     * 
     * @param int $x X channel
     * @param int $y Y channel
     * @param int $z Z channel
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function xyz(int $x, int $y, int $z): static
    {
        return new static(XYZ::class, ['x' => $x, 'y' => $y, 'z' => $z]);
    }

    /**
     * Create a Color in YCbCr color space.
     * 
     * @param int $y Y channel
     * @param int $cb Cb channel
     * @param int $cr Cr channel
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function ycbcr(int $y, int $cb, int $cr): static
    {
        return new static(YCbCr::class, ['y' => $y, 'cb' => $cb, 'cr' => $cr]);
    }

    /**
     * Create a Color from a hex string.
     * 
     * @param string $value Hex string (e.g. "#RRGGBB" or "RRGGBBAA")
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function hex(string $value, string $colorSpace = RGB::class): static
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

    /**
     * Create a Color from a named color.
     * 
     * @param string $name Named color (e.g. "red", "blue", "cyan")
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @return static
     * @throws \InvalidArgumentException
     */
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

    /**
     * Dynamically handle filter method calls.
     * 
     * @param string $name
     * @param array<int, mixed> $arguments
     * @return mixed
     * @throws \BadMethodCallException
     * @see FilterRegistry
     */
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

    /**
     * Convert the color to RGB color space.
     * 
     * @return static
     */
    abstract public function toRGB(): static;
    /**
     * Convert the color to RGBA color space.
     * 
     * @param int $alpha Alpha channel (0-255)
     * @return static
     */
    abstract public function toRGBA(int $alpha = 255): static;
    /**
     * Convert the color to CMYK color space.
     * 
     * @return static
     */
    abstract public function toCMYK(): static;
    /**
     * Convert the color to HSL color space.
     * 
     * @return static
     */
    abstract public function toHSL(): static;
    /**
     * Convert the color to HSLA color space.
     * 
     * @param int $alpha Alpha channel (0-255)
     * @return static
     */
    abstract public function toHSLA(int $alpha = 255): static;
    /**
     * Convert the color to HSV color space.
     * 
     * @return static
     */
    abstract public function toHSV(): static;
    /**
     * Convert the color to Lab color space.
     * 
     * @return static
     */
    abstract public function toLab(): static;
    /**
     * Convert the color to LCh color space.
     * 
     * @return static
     */
    abstract public function toLCh(): static;
    /**
     * Convert the color to XYZ color space.
     * 
     * @return static
     */
    abstract public function toXYZ(): static;
    /**
     * Convert the color to YCbCr color space.
     * 
     * @return static
     */
    abstract public function toYCbCr(): static;

    /**
     * Convert CMYK to RGB values.
     * 
     * @return array{r: int, g: int, b: int}
     */
    protected function convertCmykToRgb(): array
    {
        $c = $this->getC() / 100;
        $m = $this->getM() / 100;
        $y = $this->getY() / 100;
        $k = $this->getK() / 100;
        
        $r = 255 * (1 - $c) * (1 - $k);
        $g = 255 * (1 - $m) * (1 - $k);
        $b = 255 * (1 - $y) * (1 - $k);
        
        return [
            'r' => (int) round($r),
            'g' => (int) round($g),
            'b' => (int) round($b)
        ];
    }

    /**
     * Convert HSL to RGB values.
     * 
     * @return array{r: int, g: int, b: int}
     */
    protected function convertHslToRgb(): array
    {
        $c = (1 - abs(2 * ($this->getL() / 100) - 1)) * ($this->getS() / 100);
        $h = fmod($this->getH(), 360) / 60;
        $x = $c * (1 - abs(fmod($h, 2) - 1));
        $m = ($this->getL() / 100) - $c / 2;

        $r = $g = $b = 0;

        if ($h < 1) {
            $r = $c;
            $g = $x;
        } elseif ($h < 2) {
            $r = $x;
            $g = $c;
        } elseif ($h < 3) {
            $g = $c;
            $b = $x;
        } elseif ($h < 4) {
            $g = $x;
            $b = $c;
        } elseif ($h < 5) {
            $r = $x;
            $b = $c;
        } else {
            $r = $c;
            $b = $x;
        }

        $r = ($r + $m) * 255;
        $g = ($g + $m) * 255;
        $b = ($b + $m) * 255;

        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));

        return [
            'r' => (int) round($r),
            'g' => (int) round($g),
            'b' => (int) round($b)
        ];
    }

    /**
     * Convert HSLA to RGB values.
     * 
     * @return array{r: int, g: int, b: int}
     */
    protected function convertHslaToRgb(): array
    {
        $c = (1 - abs(2 * ($this->getL() / 100) - 1)) * ($this->getS() / 100);
        $x = $c * (1 - abs(fmod($this->getH() / 60, 2) - 1));
        $m = ($this->getL() / 100) - $c / 2;
        $r = $g = $b = 0;
        
        $h = $this->getH() / 60;
        if ($h < 1) {
            $r = $c;
            $g = $x;
            $b = 0;
        } elseif ($h < 2) {
            $r = $x;
            $g = $c;
            $b = 0;
        } elseif ($h < 3) {
            $r = 0;
            $g = $c;
            $b = $x;
        } elseif ($h < 4) {
            $r = 0;
            $g = $x;
            $b = $c;
        } elseif ($h < 5) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }
        
        $r = ($r + $m) * 255;
        $g = ($g + $m) * 255;
        $b = ($b + $m) * 255;
        
        return [
            'r' => (int) round($r),
            'g' => (int) round($g),
            'b' => (int) round($b)
        ];
    }

    /**
     * Convert HSV to RGB values.
     * 
     * @return array{r: int, g: int, b: int}
     */
    protected function convertHsvToRgb(): array
    {
        $h = fmod($this->getH(), 360);
        $s = $this->getS() / 100;
        $v = $this->getV() / 100;

        $c = $v * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $v - $c;

        $r = $g = $b = 0;

        if ($h < 60) {
            $r = $c; $g = $x; $b = 0;
        } elseif ($h < 120) {
            $r = $x; $g = $c; $b = 0;
        } elseif ($h < 180) {
            $r = 0; $g = $c; $b = $x;
        } elseif ($h < 240) {
            $r = 0; $g = $x; $b = $c;
        } elseif ($h < 300) {
            $r = $x; $g = 0; $b = $c;
        } else {
            $r = $c; $g = 0; $b = $x;
        }

        $r = max(0, min(255, ($r + $m) * 255));
        $g = max(0, min(255, ($g + $m) * 255));
        $b = max(0, min(255, ($b + $m) * 255));

        return [
            'r' => (int) round($r),
            'g' => (int) round($g),
            'b' => (int) round($b)
        ];
    }

    /**
     * Convert XYZ to RGB values (with gamma correction).
     * 
     * @return array{r: int, g: int, b: int}
     */
    protected function convertXyzToRgb(): array
    {
        $x = $this->getX() / 100;
        $y = $this->getY() / 100;
        $z = $this->getZ() / 100;

        // sRGB D65 conversion matrix (linear)
        $matrix = [
            [3.2404542, -1.5371385, -0.4985314],
            [-0.9692660, 1.8760108, 0.0415560],
            [0.0556434, -0.2040259, 1.0572252]
        ];

        $r = $x * $matrix[0][0] + $y * $matrix[0][1] + $z * $matrix[0][2];
        $g = $x * $matrix[1][0] + $y * $matrix[1][1] + $z * $matrix[1][2];
        $b = $x * $matrix[2][0] + $y * $matrix[2][1] + $z * $matrix[2][2];

        // Apply gamma correction
        $rgb = [$r, $g, $b];
        foreach ($rgb as &$val) {
            if ($val <= 0.0031308) {
                $val = 12.92 * $val;
            } else {
                $val = 1.055 * pow($val, 1 / 2.4) - 0.055;
            }
            $val = $val * 255;
        }
        unset($val);

        return [
            'r' => (int) round($rgb[0]),
            'g' => (int) round($rgb[1]),
            'b' => (int) round($rgb[2])
        ];
    }

    /**
     * Convert Lab to RGB values (via XYZ).
     * 
     * @return array{r: int, g: int, b: int}
     */
    protected function convertLabToRgb(): array
    {
        $l = $this->getL();
        $a = $this->getA();
        $b = $this->getB();

        // Reference white D65
        $refX = 95.047;
        $refY = 100.000;
        $refZ = 108.883;

        // Lab -> XYZ
        $y = ($l + 16) / 116;
        $x = $a / 500 + $y;
        $z = $y - $b / 200;

        $x3 = pow($x, 3);
        $y3 = pow($y, 3);
        $z3 = pow($z, 3);

        $x = $refX * (($x3 > 0.008856) ? $x3 : (($x - 16/116) / 7.787));
        $y = $refY * (($y3 > 0.008856) ? $y3 : (($y - 16/116) / 7.787));
        $z = $refZ * (($z3 > 0.008856) ? $z3 : (($z - 16/116) / 7.787));

        // Scale XYZ to 0–1 for sRGB conversion
        $x = $x / 100;
        $y = $y / 100;
        $z = $z / 100;

        // XYZ -> linear RGB
        $matrix = [
            [3.2404542, -1.5371385, -0.4985314],
            [-0.9692660, 1.8760108, 0.0415560],
            [0.0556434, -0.2040259, 1.0572252]
        ];

        $r = $x * $matrix[0][0] + $y * $matrix[0][1] + $z * $matrix[0][2];
        $g = $x * $matrix[1][0] + $y * $matrix[1][1] + $z * $matrix[1][2];
        $b = $x * $matrix[2][0] + $y * $matrix[2][1] + $z * $matrix[2][2];

        // Apply gamma correction
        $rgb = [$r, $g, $b];
        foreach ($rgb as &$val) {
            if ($val <= 0.0031308) {
                $val = 12.92 * $val;
            } else {
                $val = 1.055 * pow($val, 1 / 2.4) - 0.055;
            }
            $val = $val * 255;
        }
        unset($val);

        return [
            'r' => (int) round($rgb[0]),
            'g' => (int) round($rgb[1]),
            'b' => (int) round($rgb[2])
        ];
    }

    /**
     * Convert LCh to RGB values (via Lab and XYZ).
     * 
     * @return array{r: int, g: int, b: int}
     */
    protected function convertLchToRgb(): array
    {
        $l = $this->getL();
        $c = $this->getC();
        $h = deg2rad($this->getH());

        // LCh -> Lab
        $a = cos($h) * $c;
        $b = sin($h) * $c;

        // Lab -> XYZ
        $refX = 95.047; $refY = 100.000; $refZ = 108.883;
        $y = ($l + 16) / 116;
        $x = $a / 500 + $y;
        $z = $y - $b / 200;

        $x3 = pow($x, 3); $y3 = pow($y, 3); $z3 = pow($z, 3);
        $x = $refX * (($x3 > 0.008856) ? $x3 : (($x - 16/116) / 7.787));
        $y = $refY * (($y3 > 0.008856) ? $y3 : (($y - 16/116) / 7.787));
        $z = $refZ * (($z3 > 0.008856) ? $z3 : (($z - 16/116) / 7.787));

        // XYZ -> linear RGB
        $x /= 100; $y /= 100; $z /= 100;
        $r = $x * 3.2404542 + $y * -1.5371385 + $z * -0.4985314;
        $g = $x * -0.9692660 + $y * 1.8760108 + $z * 0.0415560;
        $b = $x * 0.0556434 + $y * -0.2040259 + $z * 1.0572252;

        // Gamma correction
        $rgb = [$r, $g, $b];
        foreach ($rgb as &$val) {
            $val = ($val <= 0.0031308) ? 12.92 * $val : 1.055 * pow($val, 1/2.4) - 0.055;
            $val *= 255;
        }
        unset($val);

        return [
            'r' => (int) round($rgb[0]),
            'g' => (int) round($rgb[1]),
            'b' => (int) round($rgb[2])
        ];
    }

    /**
     * Convert YCbCr to RGB values.
     * 
     * @return array{r: int, g: int, b: int}
     */
    protected function convertYcbcrToRgb(): array
    {
        $y = $this->getY();
        $cb = $this->getCb();
        $cr = $this->getCr();

        // Scale Y to 0-255
        $yScaled = $y * 255 / 100;

        // Convert to RGB using standard formula for centered Cb/Cr
        $r = $yScaled + 1.402 * $cr;
        $g = $yScaled - 0.344136 * $cb - 0.714136 * $cr;
        $b = $yScaled + 1.772 * $cb;

        // Clamp RGB values to 0-255
        return [
            'r' => (int) round(max(0, min(255, $r))),
            'g' => (int) round(max(0, min(255, $g))),
            'b' => (int) round(max(0, min(255, $b)))
        ];
    }

    /**
     * Convert RGB to CMYK values.
     * 
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @return array{c: int, m: int, y: int, k: int}
     */
    protected function convertRgbToCmyk(int $r, int $g, int $b): array
    {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $k = 1 - max($r, $g, $b);
        if ($k == 1) {
            return ['c' => 0, 'm' => 0, 'y' => 0, 'k' => 100];
        }

        $c = max(0, min(1, (1 - $r - $k) / (1 - $k)));
        $m = max(0, min(1, (1 - $g - $k) / (1 - $k)));
        $y = max(0, min(1, (1 - $b - $k) / (1 - $k)));

        return [
            'c' => (int) round($c * 100),
            'm' => (int) round($m * 100),
            'y' => (int) round($y * 100),
            'k' => (int) round($k * 100)
        ];
    }

    /**
     * Convert RGB to HSL values.
     * 
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @return array{h: int, s: int, l: int}
     */
    protected function convertRgbToHsl(int $r, int $g, int $b): array
    {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        
        $h = 0;
        $s = 0;
        $d = $max - $min;
        
        if ($d != 0.0) {
            $s = ($l > 0.5)
                ? $d / (2 - $max - $min)
                : $d / ($max + $min);
        
            if ($max === $r) {
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
            } elseif ($max === $g) {
                $h = ($b - $r) / $d + 2;
            } else {
                $h = ($r - $g) / $d + 4;
            }
        
            $h /= 6;
        }
        
        $h = fmod($h * 360, 360);
        $s = max(0, min(100, $s * 100));
        $l = max(0, min(100, $l * 100));
        
        return [
            'h' => (int) round($h),
            's' => (int) round($s),
            'l' => (int) round($l)
        ];
    }

    /**
     * Convert RGB to HSV values.
     * 
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @return array{h: int, s: int, v: int}
     */
    protected function convertRgbToHsv(int $r, int $g, int $b): array
    {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $v = $max;
        
        $d = $max - $min;
        $s = ($max == 0.0) ? 0.0 : $d / $max;
        
        $h = 0.0;
        
        if ($d != 0.0) {
            if ($max === $r) {
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
            } elseif ($max === $g) {
                $h = ($b - $r) / $d + 2;
            } else {
                $h = ($r - $g) / $d + 4;
            }
            $h /= 6;
        }
        
        $h = fmod($h * 360, 360);
        $s = max(0, min(100, $s * 100));
        $v = max(0, min(100, $v * 100));
        
        return [
            'h' => (int) round($h),
            's' => (int) round($s),
            'v' => (int) round($v)
        ];
    }

    /**
     * Convert RGB to XYZ values.
     * 
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @return array{x: float, y: float, z: float}
     */
    protected function convertRgbToXyz(int $r, int $g, int $b): array
    {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        // Inverse gamma (linearization)
        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Linear RGB → XYZ
        $matrix = [
            [0.4124564, 0.3575761, 0.1804375],
            [0.2126729, 0.7151522, 0.0721750],
            [0.0193339, 0.1191920, 0.9503041]
        ];

        $x = $r * $matrix[0][0] + $g * $matrix[0][1] + $b * $matrix[0][2];
        $y = $r * $matrix[1][0] + $g * $matrix[1][1] + $b * $matrix[1][2];
        $z = $r * $matrix[2][0] + $g * $matrix[2][1] + $b * $matrix[2][2];

        // Scale to 0–100 (percentage)
        return [
            'x' => round($x * 100, 4),
            'y' => round($y * 100, 4),
            'z' => round($z * 100, 4)
        ];
    }

    /**
     * Convert RGB to Lab values (via XYZ).
     * 
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @return array{l: float, a: float, b: float}
     */
    protected function convertRgbToLab(int $r, int $g, int $b): array
    {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        // Inverse gamma
        $r = ($r > 0.04045) ? pow(($r + 0.055)/1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055)/1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055)/1.055, 2.4) : $b / 12.92;

        // RGB -> XYZ
        $x = $r*0.4124564 + $g*0.3575761 + $b*0.1804375;
        $y = $r*0.2126729 + $g*0.7151522 + $b*0.0721750;
        $z = $r*0.0193339 + $g*0.1191920 + $b*0.9503041;

        // Normalize for D65
        $x /= 0.95047;
        $y /= 1.00000;
        $z /= 1.08883;

        $delta = 6/29;

        $fx = ($x > $delta**3) ? pow($x, 1/3) : ($x/(3*$delta*$delta) + 4/29);
        $fy = ($y > $delta**3) ? pow($y, 1/3) : ($y/(3*$delta*$delta) + 4/29);
        $fz = ($z > $delta**3) ? pow($z, 1/3) : ($z/(3*$delta*$delta) + 4/29);

        $l = 116*$fy - 16;
        $a = 500*($fx - $fy);
        $b = 200*($fy - $fz);

        return ['l' => $l, 'a' => $a, 'b' => $b];
    }

    /**
     * Convert RGB to LCh values (via Lab).
     * 
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @return array{l: float, c: float, h: float}
     */
    protected function convertRgbToLch(int $r, int $g, int $b): array
    {
        $lab = $this->convertRgbToLab($r, $g, $b);
        $l = $lab['l'];
        $a = $lab['a'];
        $b = $lab['b'];
        
        $c = sqrt($a * $a + $b * $b);
        $h = rad2deg(atan2($b, $a));
        if ($h < 0) {
            $h += 360;
        }
        
        return ['l' => $l, 'c' => $c, 'h' => $h];
    }

    /**
     * Convert RGB to YCbCr values.
     * 
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @return array{y: int, cb: int, cr: int}
     */
    protected function convertRgbToYcbcr(int $r, int $g, int $b): array
    {
        // Compute Y, Cb, Cr using standard linear formula
        // Using 8-bit RGB (0-255)
        $y  = 0.299 * $r + 0.587 * $g + 0.114 * $b;
        $cb = -0.168736 * $r - 0.331264 * $g + 0.5 * $b;
        $cr = 0.5 * $r - 0.418688 * $g - 0.081312 * $b;

        // Adjust ranges: Y: 0-100, Cb/Cr: -128 → 127
        $y  = $y * 100 / 255;   // scale Y to 0-100
        // Cb/Cr are already centered at 0, no +128 offset

        // Clamp values to valid ranges
        $y  = max(0, min(100, $y));
        $cb = max(-128, min(127, $cb));
        $cr = max(-128, min(127, $cr));

        return [
            'y' => (int) round($y),
            'cb' => (int) round($cb),
            'cr' => (int) round($cr)
        ];
    }

    /**
     * Convert the color to a hex string.
     * 
     * @return string
     * @throws \InvalidArgumentException
     */
    final public function toHex(): string
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

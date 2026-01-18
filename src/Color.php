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
                $r = 255 * (1 - $this->getC() / 100) * (1 - $this->getK() / 100);
                $g = 255 * (1 - $this->getM() / 100) * (1 - $this->getK() / 100);
                $b = 255 * (1 - $this->getY() / 100) * (1 - $this->getK() / 100);
                return self::rgb((int)$r, (int)$g, (int)$b);
            case HSL::class:
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

                return self::rgb((int)round($r), (int)round($g), (int)round($b));
            case HSLA::class:
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
                return self::rgb((int)round($r), (int)round($g), (int)round($b));
            case HSV::class:
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

                return self::rgb(
                    (int) round($r),
                    (int) round($g),
                    (int) round($b)
                );
            case Lab::class:
                $l = $this->getL();
                $a = $this->getA();
                $b = $this->getB();
                /*
                 * Convert Lab to XYZ
                 * Reference white D65
                 */
                $refX = 95.047;
                $refY = 100.000;
                $refZ = 108.883;

                $y = ($l + 16) / 116;
                $x = $a / 500 + $y;
                $z = $y - $b / 200;

                $x3 = pow($x, 3);
                $z3 = pow($z, 3);

                $x = $refX * (($x3 > 0.008856) ? $x3 : (($x - 16/116) / 7.787));
                $y = $refY * ((pow($y, 3) > 0.008856) ? pow($y, 3) : (($y - 16/116) / 7.787));
                $z = $refZ * (($z3 > 0.008856) ? $z3 : (($z - 16/116) / 7.787));

                // Now convert XYZ to RGB (sRGB D65)
                $x = $x / 100;
                $y = $y / 100;
                $z = $z / 100;

                $r = $x * 3.2404542 + $y * -1.5371385 + $z * -0.4985314;
                $g = $x * -0.9692660 + $y * 1.8760108 + $z * 0.0415560;
                $b = $x * 0.0556434 + $y * -0.2040259 + $z * 1.0572252;

                // Apply gamma correction
                $rgb = [$r, $g, $b];
                foreach ($rgb as &$val) {
                    if ($val <= 0.0031308) {
                        $val = 12.92 * $val;
                    } else {
                        $val = 1.055 * pow($val, 1 / 2.4) - 0.055;
                    }
                    $val = max(0, min(1, $val)); // Clamp between 0 and 1
                    $val = (int)round($val * 255);
                }
                unset($val);

                return self::rgb($rgb[0], $rgb[1], $rgb[2]);
            case LCh::class:
                $l = $this->getL();
                $c = $this->getC();
                $h = deg2rad($this->getH());
                $a = cos($h) * $c;
                $b = sin($h) * $c;

                // Now convert Lab to XYZ
                $refX = 95.047;
                $refY = 100.000;
                $refZ = 108.883;
                $y = ($l + 16) / 116;
                $x = $a / 500 + $y;
                $z = $y - $b / 200;
                $x3 = pow($x, 3);
                $z3 = pow($z, 3);
                $x = $refX * (($x3 > 0.008856) ? $x3 : (($x - 16/116) / 7.787));
                $y = $refY * ((pow($y, 3) > 0.008856) ? pow($y, 3) : (($y - 16/116) / 7.787));
                $z = $refZ * (($z3 > 0.008856) ? $z3 : (($z - 16/116) / 7.787));

                // Now convert XYZ to RGB (sRGB D65)
                $x = $x / 100;
                $y = $y / 100;
                $z = $z / 100;
                $r = $x * 3.2404542 + $y * -1.5371385 + $z * -0.4985314;
                $g = $x * -0.9692660 + $y * 1.8760108 + $z * 0.0415560;
                $b = $x * 0.0556434 + $y * -0.2040259 + $z * 1.0572252;

                // Apply gamma correction
                $rgb = [$r, $g, $b];
                foreach ($rgb as &$val) {
                    if ($val <= 0.0031308) {
                        $val = 12.92 * $val;
                    } else {
                        $val = 1.055 * pow($val, 1 / 2.4) - 0.055;
                    }
                    $val = max(0, min(1, $val)); // Clamp between 0 and 1
                    $val = (int)round($val * 255);
                }
                unset($val);

                return self::rgb($rgb[0], $rgb[1], $rgb[2]);
            case XYZ::class:
                $x = $this->getX() / 100;
                $y = $this->getY() / 100;
                $z = $this->getZ() / 100;

                // sRGB D65 conversion matrix (more precise values)
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
                    $val = max(0, min(1, $val)); // Clamp between 0 and 1
                    $val = (int)round($val * 255);
                }
                unset($val);

                return self::rgb($rgb[0], $rgb[1], $rgb[2]);
            case YCbCr::class:
                $y = $this->getY();
                $cb = $this->getCb();
                $cr = $this->getCr();

                // Scale Y to 0-255
                $yScaled = $y * 255 / 100;

                // Convert to RGB using standard formula for centered Cb/Cr
                $r = $yScaled + 1.402 * $cr;
                $g = $yScaled - 0.344136 * $cb - 0.714136 * $cr;
                $b = $yScaled + 1.772 * $cb;

                // Clamp RGB values to 0-255 and round
                $r = (int) round(max(0, min(255, $r)));
                $g = (int) round(max(0, min(255, $g)));
                $b = (int) round(max(0, min(255, $b)));

                return self::rgb($r, $g, $b);
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
        /** @var RGB $rgbSpace */
        $r = $rgb->getR() / 255;
        $g = $rgb->getG() / 255;
        $b = $rgb->getB() / 255;

        $k = 1 - max($r, $g, $b);
        if ($k == 1) {
            return self::cmyk(0, 0, 0, 100);
        }

        $c = (1 - $r - $k) / (1 - $k);
        $m = (1 - $g - $k) / (1 - $k);
        $y = (1 - $b - $k) / (1 - $k);

        return self::cmyk(
            (int)round($c * 100),
            (int)round($m * 100),
            (int)round($y * 100),
            (int)round($k * 100)
        );
    }

    #[\Override]
    public function toHSL(): static
    {
        $rgb = $this->toRGB();
        $r = $rgb->getR() / 255;
        $g = $rgb->getG() / 255;
        $b = $rgb->getB() / 255;
        
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
        
        return self::hsl(
            (int) round($h),
            (int) round($s),
            (int) round($l)
        );
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
        $r = $rgb->getR() / 255;
        $g = $rgb->getG() / 255;
        $b = $rgb->getB() / 255;
        
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
        
        return self::hsv(
            (int) round($h),
            (int) round($s),
            (int) round($v)
        );
    }

    #[\Override]
    public function toLab(): static
    {
        $rgb = $this->toRGB();
        $r = $rgb->getR() / 255;
        $g = $rgb->getG() / 255;
        $b = $rgb->getB() / 255;

        // Apply inverse gamma correction
        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Convert to XYZ (sRGB D65, more precise values)
        $x = $r * 0.4124564 + $g * 0.3575761 + $b * 0.1804375;
        $y = $r * 0.2126729 + $g * 0.7151522 + $b * 0.0721750;
        $z = $r * 0.0193339 + $g * 0.1191920 + $b * 0.9503041;

        // Normalize for D65 white point
        $x /= 0.95047;
        $y /= 1.00000;
        $z /= 1.08883;

        // Convert to Lab
        $fx = ($x > 0.008856) ? pow($x, 1/3) : (7.787 * $x) + (16 / 116);
        $fy = ($y > 0.008856) ? pow($y, 1/3) : (7.787 * $y) + (16 / 116);
        $fz = ($z > 0.008856) ? pow($z, 1/3) : (7.787 * $z) + (16 / 116);

        $l = (116 * $fy) - 16;
        $a = 500 * ($fx - $fy);
        $b = 200 * ($fy - $fz);

        return self::lab(
            (int)round($l),
            (int)round($a),
            (int)round($b)
        );
    }

    #[\Override]
    public function toLCh(): static
    {
        $lab = $this->toLab();
        $l = $lab->getL();
        $a = $lab->getA();
        $b = $lab->getB();

        $c = sqrt($a * $a + $b * $b);
        $h = atan2($b, $a);
        $h = rad2deg($h);
        if ($h < 0) {
            $h += 360;
        }

        return self::lch(
            max(0, min ((int)round($l), 100)),
            max(0, min ((int)round($c), 100)),
            (int)round($h)
        );
    }

    #[\Override]
    public function toXYZ(): static
    {
        $rgb = $this->toRGB();
        $r = $rgb->getR() / 255;
        $g = $rgb->getG() / 255;
        $b = $rgb->getB() / 255;

        // Apply inverse gamma correction
        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Convert to XYZ (sRGB D65, more precise values)
        $matrix = [
            [0.4124564, 0.3575761, 0.1804375],
            [0.2126729, 0.7151522, 0.0721750],
            [0.0193339, 0.1191920, 0.9503041]
        ];
        $x = $r * $matrix[0][0] + $g * $matrix[0][1] + $b * $matrix[0][2];
        $y = $r * $matrix[1][0] + $g * $matrix[1][1] + $b * $matrix[1][2];
        $z = $r * $matrix[2][0] + $g * $matrix[2][1] + $b * $matrix[2][2];

        // Scale to the range [0, 100]
        return self::xyz(
            (int)round($x * 100, 4),
            (int)round($y * 100, 4),
            (int)round($z * 100, 4)
        );
    }

    #[\Override]
    public function toYCbCr(): static
    {
        // Get RGB values and scale to 0-255
        $r = $rgb->getR();
        $g = $rgb->getG();
        $b = $rgb->getB();

        // Compute Y, Cb, Cr using standard linear formula
        // Using 8-bit RGB (0-255)
        $y  = 0.299 * $r + 0.587 * $g + 0.114 * $b;
        $cb = -0.168736 * $r - 0.331264 * $g + 0.5 * $b;
        $cr = 0.5 * $r - 0.418688 * $g - 0.081312 * $b;

        // Adjust ranges to your internal YCbCr:
        // Y: 0-100, Cb/Cr: -128 → 127
        $y  = $y * 100 / 255;   // scale Y to 0-100
        // Cb/Cr are already centered at 0 in your model, no +128 offset
        $cb = $cb;
        $cr = $cr;

        // Clamp values to valid ranges
        $y  = max(0, min(100, $y));
        $cb = max(-128, min(127, $cb));
        $cr = max(-128, min(127, $cr));

        return self::ycbcr(round($y), round($cb), round($cr));
    }
}

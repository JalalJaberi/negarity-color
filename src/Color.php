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

final class Color
{
    public function __construct(
        private readonly ColorSpaceInterface $colorSpace
    ) {
    }

    public function getColorSpace(): ColorSpaceInterface
    {
        return $this->colorSpace;
    }

    public static function rgb(int $r, int $g, int $b): self
    {
        return new self(new RGB($r, $g, $b));
    }

    public static function rgba(int $r, int $g, int $b, float $a): self
    {
        return new self(new RGBA($r, $g, $b, $a));
    }

    public static function cmyk(int $c, int $m, int $y, int $k): self
    {
        return new self(new CMYK($c, $m, $y, $k));
    }

    public static function hsl(int $h, int $s, int $l): self
    {
        return new self(new HSL($h, $s, $l));
    }

    public static function hsla(int $h, int $s, int $l, float $a): self
    {
        return new self(new HSLA($h, $s, $l, $a));
    }

    public static function hsv(int $h, int $s, int $v): self
    {
        return new self(new HSV($h, $s, $v));
    }

    public static function lab(float $l, float $a, float $b): self
    {
        return new self(new Lab($l, $a, $b));
    }

    public static function lch(float $l, float $c, float $h): self
    {
        return new self(new LCh($l, $c, $h));
    }

    public static function xyz(float $x, float $y, float $z): self
    {
        return new self(new XYZ($x, $y, $z));
    }

    public static function ycbcr(int $y, int $cb, int $cr): self
    {
        return new self(new YCbCr($y, $cb, $cr));
    }

    public function toRGB(): Color
    {
        switch (get_class($this->colorSpace)) {
            case RGB::class:
                return new self($this);
            case RGBA::class:
                /** @var RGBA $rgba */
                $rgba = $this->colorSpace;
                return self::rgb($rgba->getR(), $rgba->getG(), $rgba->getB());
            case CMYK::class:
                /** @var CMYK $cmyk */
                $cmyk = $this->colorSpace;
                $r = 255 * (1 - $cmyk->getC() / 100) * (1 - $cmyk->getK() / 100);
                $g = 255 * (1 - $cmyk->getM() / 100) * (1 - $cmyk->getK() / 100);
                $b = 255 * (1 - $cmyk->getY() / 100) * (1 - $cmyk->getK() / 100);
                return self::rgb((int)$r, (int)$g, (int)$b);
            case HSL::class:
                /** @var HSL $hsl */
                $hsl = $this->colorSpace;
                $c = (1 - abs(2 * ($hsl->getL() / 100) - 1)) * ($hsl->getS() / 100);
                $x = $c * (1 - abs(fmod($hsl->getH() / 60, 2) - 1));
                $m = ($hsl->getL() / 100) - $c / 2;
                $r = $g = $b = 0;
                if ($hsl->getH() < 60) {
                    $r = $c;
                    $g = $x;
                } elseif ($hsl->getH() < 120) {
                    $r = $x;
                    $g = $c;
                }
                $r = ($r + $m) * 255;
                $g = ($g + $m) * 255;
                $b = ($b + $m) * 255;
                return self::rgb((int)$r, (int)$g, (int)$b);
            case HSLA::class:
                /** @var HSLA $hsla */
                $hsla = $this->colorSpace;
                $c = (1 - abs(2 * ($hsla->getL() / 100) - 1)) * ($hsla->getS() / 100);
                $x = $c * (1 - abs(fmod($hsla->getH() / 60, 2) - 1));
                $m = ($hsla->getL() / 100) - $c / 2;
                $r = $g = $b = 0;
                if ($hsla->getH() < 60) {
                    $r = $c;
                    $g = $x;
                } elseif ($hsla->getH() < 120) {
                    $r = $x;
                    $g = $c;
                }
                $r = ($r + $m) * 255;
                $g = ($g + $m) * 255;
                $b = ($b + $m) * 255;
                return self::rgb((int)$r, (int)$g, (int)$b);
            case HSV::class:
                /** @var HSV $hsv */
                $hsv = $this->colorSpace;
                $c = ($hsv->getV() / 100) * ($hsv->getS() / 100);
                $x = $c * (1 - abs(fmod($hsv->getH() / 60, 2) - 1));
                $m = ($hsv->getV() / 100) - $c;
                $r = $g = $b = 0;
                if ($hsv->getH() < 60) {
                    $r = $c;
                    $g = $x;
                } elseif ($hsv->getH() < 120) {
                    $r = $x;
                    $g = $c;
                }
                $r = ($r + $m) * 255;
                $g = ($g + $m) * 255;
                $b = ($b + $m) * 255;
                return self::rgb((int)$r, (int)$g, (int)$b);
            case Lab::class:
                /** @var Lab $lab */
                $lab = $this->colorSpace;
                $l = $lab->getL();
                $a = $lab->getA();
                $b = $lab->getB();
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

                $r = $x * 3.2406 + $y * -1.5372 + $z * -0.4986;
                $g = $x * -0.9689 + $y * 1.8758 + $z * 0.0415;
                $b = $x * 0.0557 + $y * -0.2040 + $z * 1.0570;

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
                /** @var LCh $lch */
                $lch = $this->colorSpace;
                $l = $lch->getL();
                $c = $lch->getC();
                $h = deg2rad($lch->getH());
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
                $r = $x * 3.2406 + $y * -1.5372 + $z * -0.4986;
                $g = $x * -0.9689 + $y * 1.8758 + $z * 0.0415;
                $b = $x * 0.0557 + $y * -0.2040 + $z * 1.0570;

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
                /** @var XYZ $xyz */
                $xyz = $this->colorSpace;
                $x = $xyz->getX();
                $y = $xyz->getY();
                $z = $xyz->getZ();
                // implement it even if it's neeeded a intermediate convrersion to RGB
                $x = $x / 100;
                $y = $y / 100;
                $z = $z / 100;

                // sRGB D65 conversion matrix
                $r = $x * 3.2406 + $y * -1.5372 + $z * -0.4986;
                $g = $x * -0.9689 + $y * 1.8758 + $z * 0.0415;
                $b = $x * 0.0557 + $y * -0.2040 + $z * 1.0570;

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
                /** @var YCbCr $ycbcr */
                $ycbcr = $this->colorSpace;
                $y = $ycbcr->getY();
                $cb = $ycbcr->getCb();
                $cr = $ycbcr->getCr();
                $r = $y + 1.402 * ($cr - 128);
                $g = $y - 0.344136 * ($cb - 128) - 0.714136 * ($cr - 128);
                $b = $y + 1.772 * ($cb - 128);
                return self::rgb((int)max(0, min(255, $r)), (int)max(0, min(255, $g)), (int)max(0, min(255, $b)));
            default:
                throw new \RuntimeException('Conversion to RGB not implemented for this color space.');
        }
    }
}

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

final class Color extends ColorBase
{
    public function __construct(ColorSpaceInterface $colorSpace)
    {
        parent::__construct($colorSpace);
    }

    public function toRGB(): static
    {
        switch (get_class($this->colorSpace)) {
            case RGB::class:
                return new self($this->colorSpace);
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

    public function toRGBA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new \InvalidArgumentException('Alpha value must be between 0 and 255');
        }

        switch (get_class($this->colorSpace)) {
            case RGBA::class:
                return new self($this->colorSpace);
            case RGB::class:
                /** @var RGB $rgb */
                $rgb = $this->colorSpace;
                return new self(new RGBA($rgb->getR(), $rgb->getG(), $rgb->getB(), $alpha));
            case HSLA::class:
                /** @var HSLA $hsla */
                $hsla = $this->colorSpace;
                $rgb = $hsla->toRGB();
                return new self(new RGBA($rgb->getR(), $rgb->getG(), $rgb->getB(), $hsla->getA()));
            default:
                /** @var RGB $rgb */
                $rgb = $this->toRGB();
                return new self(new RGBA($rgb->getR(), $rgb->getG(), $rgb->getB(), $alpha));
        }
    }

    public function toCMYK(): static
    {
        $rgb = $this->toRGB();
        /** @var RGB $rgbSpace */
        $rgbSpace = $rgb->getColorSpace();
        $r = $rgbSpace->getR() / 255;
        $g = $rgbSpace->getG() / 255;
        $b = $rgbSpace->getB() / 255;

        $k = 1 - max($r, $g, $b);
        if ($k == 1) {
            return new self(new CMYK(0, 0, 0, 100));
        }

        $c = (1 - $r - $k) / (1 - $k);
        $m = (1 - $g - $k) / (1 - $k);
        $y = (1 - $b - $k) / (1 - $k);

        return new self(new CMYK(
            (int)round($c * 100),
            (int)round($m * 100),
            (int)round($y * 100),
            (int)round($k * 100)
        ));
    }

    public function toHSL(): static
    {
        $rgb = $this->toRGB();
        /** @var RGB $rgbSpace */
        $rgbSpace = $rgb->getColorSpace();
        $r = $rgbSpace->getR() / 255;
        $g = $rgbSpace->getG() / 255;
        $b = $rgbSpace->getB() / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0; // achromatic
        } else {
            $d = $max - $min;
            $s = ($l > 0.5) ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + (($g < $b) ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            $h /= 6;
        }

        return new self(new HSL(
            (int)round($h * 360),
            (int)round($s * 100),
            (int)round($l * 100)
        ));
    }

    public function toHSLA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new \InvalidArgumentException('Alpha value must be between 0 and 255');
        }

        switch (get_class($this->colorSpace)) {
            case HSLA::class:
                return new self($this->colorSpace);
            case HSL::class:
                /** @var HSL $hsl */
                $hsl = $this->colorSpace;
                return new self(new HSLA(
                    $hsl->getH(),
                    $hsl->getS(),
                    $hsl->getL(),
                    $alpha
                ));
            case RGBA::class:
                /** @var RGBA $rgba */
                $rgba = $this->colorSpace;
                $rgbColor = self::rgb($rgba->getR(), $rgba->getG(), $rgba->getB());
                $hslColor = $rgbColor->toHSL();
                /** @var HSL $hslSpace */
                $hslSpace = $hslColor->getColorSpace();
                return new self(new HSLA(
                    $hslSpace->getH(),
                    $hslSpace->getS(),
                    $hslSpace->getL(),
                    $rgba->getA()
                ));
            default:
                /** @var HSL $hsl */
                $hsl = $this->toHSL();
                /** @var HSL $hslSpace */
                $hslSpace = $hsl->getColorSpace();
                return new self(new HSLA(
                    $hslSpace->getH(),
                    $hslSpace->getS(),
                    $hslSpace->getL(),
                    $alpha
                ));
        }
    }

    public function toHSV(): static
    {
        $rgb = $this->toRGB();
        /** @var RGB $rgbSpace */
        $rgbSpace = $rgb->getColorSpace();
        $r = $rgbSpace->getR() / 255;
        $g = $rgbSpace->getG() / 255;
        $b = $rgbSpace->getB() / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $v = $max;

        $d = $max - $min;
        $s = ($max == 0) ? 0 : $d / $max;

        if ($max == $min) {
            $h = 0; // achromatic
        } else {
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + (($g < $b) ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            $h /= 6;
        }

        return new self(new HSV(
            (int)round($h * 360),
            (int)round($s * 100),
            (int)round($v * 100)
        ));
    }

    public function toLab(): static
    {
        $rgb = $this->toRGB();
        /** @var RGB $rgbSpace */
        $rgbSpace = $rgb->getColorSpace();
        $r = $rgbSpace->getR() / 255;
        $g = $rgbSpace->getG() / 255;
        $b = $rgbSpace->getB() / 255;

        // Apply inverse gamma correction
        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Convert to XYZ
        $x = $r * 0.4124 + $g * 0.3576 + $b * 0.1805;
        $y = $r * 0.2126 + $g * 0.7152 + $b * 0.0722;
        $z = $r * 0.0193 + $g * 0.1192 + $b * 0.9505;

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

        return new self(new Lab(
            (int)round($l),
            (int)round($a),
            (int)round($b)
        ));
    }

    public function toLCh(): static
    {
        $labColor = $this->toLab();
        /** @var Lab $labSpace */
        $labSpace = $labColor->getColorSpace();
        $l = $labSpace->getL();
        $a = $labSpace->getA();
        $b = $labSpace->getB();

        $c = sqrt($a * $a + $b * $b);
        $h = atan2($b, $a);
        $h = rad2deg($h);
        if ($h < 0) {
            $h += 360;
        }

        return new self(new LCh(
            max(0, min ((int)round($l), 100)),
            max(0, min ((int)round($c), 100)),
            (int)round($h)
        ));
    }

    public function toXYZ(): static
    {
        $rgb = $this->toRGB();
        /** @var RGB $rgbSpace */
        $rgbSpace = $rgb->getColorSpace();
        $r = $rgbSpace->getR() / 255;
        $g = $rgbSpace->getG() / 255;
        $b = $rgbSpace->getB() / 255;

        // Apply inverse gamma correction
        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Convert to XYZ
        $x = $r * 0.4124 + $g * 0.3576 + $b * 0.1805;
        $y = $r * 0.2126 + $g * 0.7152 + $b * 0.0722;
        $z = $r * 0.0193 + $g * 0.1192 + $b * 0.9505;

        // Scale to the range [0, 100]
        return new self(new XYZ(
            (int)round($x * 100, 4),
            (int)round($y * 100, 4),
            (int)round($z * 100, 4)
        ));
    }

    public function toYCbCr(): static
    {
        $rgb = $this->toRGB();
        /** @var RGB $rgbSpace */
        $rgbSpace = $rgb->getColorSpace();
        $r = $rgbSpace->getR();
        $g = $rgbSpace->getG();
        $b = $rgbSpace->getB();

        $y  = (int)round(0.299 * $r + 0.587 * $g + 0.114 * $b);
        $cb = (int)round(128 - 0.168736 * $r - 0.331264 * $g + 0.5 * $b);
        $cr = (int)round(128 + 0.5 * $r - 0.460525 * $g - 0.081475 * $b);

        return new self(new YCbCr(
            max(0, min(255, $y)),
            max(0, min(255, $cb)),
            max(0, min(255, $cr))
        ));
    }
}

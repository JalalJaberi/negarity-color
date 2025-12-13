<?php

namespace Negarity\Color\Filter\Parameterized;

use Negarity\Color\Color;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

class BrightnessFilter implements ParameterizedColorFilterInterface
{
    public function getName(): string { return 'brightness'; }

    public function apply(Color $color, mixed $value): Color
    {
        $factor = (int)$value;

        // check the color space first and then do the calculation based on the color space
        switch ($color->getColorSpace()->getName()) {
            case ColorSpaceEnum::RGB->value:
                $r = max(0, min(255, $color->getChannel('r') + $factor));
                $g = max(0, min(255, $color->getChannel('g') + $factor));
                $b = max(0, min(255, $color->getChannel('b') + $factor));
                return Color::rgb($r, $g, $b);
            case ColorSpaceEnum::RGBA->value:
                $r = max(0, min(255, $color->getChannel('r') + $factor));
                $g = max(0, min(255, $color->getChannel('g') + $factor));
                $b = max(0, min(255, $color->getChannel('b') + $factor));
                $a = $color->getChannel('a');
                return Color::rgba($r, $g, $b, $a);
            case ColorSpaceEnum::CMYK->value:
                $c = max(0, min(100, $color->getChannel('c') - ($factor / 2.55)));
                $m = max(0, min(100, $color->getChannel('m') - ($factor / 2.55)));
                $y = max(0, min(100, $color->getChannel('y') - ($factor / 2.55)));
                $k = $color->getChannel('k');
                return Color::cmyk($c, $m, $y, $k);
            case ColorSpaceEnum::HSL->value:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $h = $color->getChannel('h');
                $s = $color->getChannel('s');
                return Color::hsl($h, $s, $l);
            case ColorSpaceEnum::HSLA->value:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $h = $color->getChannel('h');
                $s = $color->getChannel('s');
                $a = $color->getChannel('a');
                return Color::hsla($h, $s, $l, $a);
            case ColorSpaceEnum::HSV->value:
                $v = max(0, min(100, $color->getChannel('v') + $factor));
                $h = $color->getChannel('h');
                $s = $color->getChannel('s');
                return Color::hsv($h, $s, $v);
            case ColorSpaceEnum::LAB->value:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $a = $color->getChannel('a');
                $b = $color->getChannel('b');
                return Color::lab($l, $a, $b);
            case ColorSpaceEnum::LCH->value:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $c = $color->getChannel('c');
                $h = $color->getChannel('h');
                return Color::lch($l, $c, $h);
            case ColorSpaceEnum::XYZ->value:
                $x = max(0, min(100, $color->getChannel('x') + $factor));
                $y = max(0, min(100, $color->getChannel('y') + $factor));
                $z = max(0, min(100, $color->getChannel('z') + $factor));
                return Color::xyz($x, $y, $z);
            case ColorSpaceEnum::YCBCR->value:
                $y = max(0, min(255, $color->getChannel('y') + $factor));
                $cb = $color->getChannel('cb');
                $cr = $color->getChannel('cr');
                return Color::ycbcr($y, $cb, $cr);
            default:
                throw new \InvalidArgumentException('Brightness filter not supported for color space: ' . $color->getColorSpace()->getName());
                break;
        }
    }
}

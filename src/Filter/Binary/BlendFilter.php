<?php

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\Color;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

class BlendFilter implements BinaryColorFilterInterface
{
    public function getName(): string { return 'blend'; }

    public function apply(Color $base, Color $blend): Color
    {
        if ($base->getColorSpace()->getName() != $blend->getColorSpace()->getName()) {
            throw new \InvalidArgumentException('Colors must be in the same color space to blend.');
        }

        switch ($base->getColorSpace()->getName()) {
            case ColorSpaceEnum::RGB->value:
                $r = (int)(($base->getChannel('r') + $blend->getChannel('r')) / 2);
                $g = (int)(($base->getChannel('g') + $blend->getChannel('g')) / 2);
                $b = (int)(($base->getChannel('b') + $blend->getChannel('b')) / 2);        
                return Color::rgb($r, $g, $b);
            case ColorSpaceEnum::RGBA->value:
                $r = (int)(($base->getChannel('r') + $blend->getChannel('r')) / 2);
                $g = (int)(($base->getChannel('g') + $blend->getChannel('g')) / 2);
                $b = (int)(($base->getChannel('b') + $blend->getChannel('b')) / 2);        
                $a = (float)(($base->getChannel('a') + $blend->getChannel('a')) / 2);
                return Color::rgba($r, $g, $b, $a);
            case ColorSpaceEnum::CMYK->value:
                $c = (float)(($base->getChannel('c') + $blend->getChannel('c')) / 2);
                $m = (float)(($base->getChannel('m') + $blend->getChannel('m')) / 2);
                $y = (float)(($base->getChannel('y') + $blend->getChannel('y')) / 2);
                $k = (float)(($base->getChannel('k') + $blend->getChannel('k')) / 2);
                return Color::cmyk($c, $m, $y, $k);
            case ColorSpaceEnum::HSL->value:
                $h = (float)(($base->getChannel('h') + $blend->getChannel('h')) / 2);
                $s = (float)(($base->getChannel('s') + $blend->getChannel('s')) / 2);
                $l = (float)(($base->getChannel('l') + $blend->getChannel('l')) / 2);
                return Color::hsl($h, $s, $l);
            case ColorSpaceEnum::HSLA->value:
                $h = (float)(($base->getChannel('h') + $blend->getChannel('h')) / 2);
                $s = (float)(($base->getChannel('s') + $blend->getChannel('s')) / 2);
                $l = (float)(($base->getChannel('l') + $blend->getChannel('l')) / 2);
                $a = (float)(($base->getChannel('a') + $blend->getChannel('a')) / 2);
                return Color::hsla($h, $s, $l, $a);
            case ColorSpaceEnum::HSV->value:
                $h = (float)(($base->getChannel('h') + $blend->getChannel('h')) / 2);
                $s = (float)(($base->getChannel('s') + $blend->getChannel('s')) / 2);
                $v = (float)(($base->getChannel('v') + $blend->getChannel('v')) / 2);
                return Color::hsv($h, $s, $v);
            case ColorSpaceEnum::LAB->value:
                $l = (float)(($base->getChannel('l') + $blend->getChannel('l')) / 2);
                $a = (float)(($base->getChannel('a') + $blend->getChannel('a')) / 2);
                $b = (float)(($base->getChannel('b') + $blend->getChannel('b')) / 2);
                return Color::lab($l, $a, $b);
            case ColorSpaceEnum::LCH->value:
                $l = (float)(($base->getChannel('l') + $blend->getChannel('l')) / 2);
                $c = (float)(($base->getChannel('c') + $blend->getChannel('c')) / 2);
                $h = (float)(($base->getChannel('h') + $blend->getChannel('h')) / 2);
                return Color::lch($l, $c, $h);
            case ColorSpaceEnum::XYZ->value:
                $x = (float)(($base->getChannel('x') + $blend->getChannel('x')) / 2);
                $y = (float)(($base->getChannel('y') + $blend->getChannel('y')) / 2);
                $z = (float)(($base->getChannel('z') + $blend->getChannel('z')) / 2);
                return Color::xyz($x, $y, $z);
            case ColorSpaceEnum::YCBCR->value:
                $y = (float)(($base->getChannel('y') + $blend->getChannel('y')) / 2);
                $cb = (float)(($base->getChannel('cb') + $blend->getChannel('cb')) / 2);
                $cr = (float)(($base->getChannel('cr') + $blend->getChannel('cr')) / 2);
                return Color::ycbcr($y, $cb, $cr);
            default:
                throw new \InvalidArgumentException('Brightness filter not supported for color space: ' . $color->getColorSpace()->getName());
                break;
        }
    }
}
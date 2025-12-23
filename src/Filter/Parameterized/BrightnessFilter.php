<?php

declare(strict_types=1);

namespace Negarity\Color\Filter\Parameterized;

use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\{
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

final class BrightnessFilter implements ParameterizedColorFilterInterface
{
    #[\Override]
    public function getName(): string { return 'brightness'; }

    #[\Override]
    public function apply(ColorInterface $color, mixed $value): ColorInterface
    {
        $factor = (int)$value;

        // @TODO: Check if the color is mutable or not
        // check the color space first and then do the calculation based on the color space
        switch ($color->getColorSpace()) {
            case RGB::class:
                $r = max(0, min(255, $color->getChannel('r') + $factor));
                $g = max(0, min(255, $color->getChannel('g') + $factor));
                $b = max(0, min(255, $color->getChannel('b') + $factor));
                return $color->with(['r' => $r, 'g' => $g, 'b' => $b]);
            case RGBA::class:
                $r = max(0, min(255, $color->getChannel('r') + $factor));
                $g = max(0, min(255, $color->getChannel('g') + $factor));
                $b = max(0, min(255, $color->getChannel('b') + $factor));
                $a = $color->getChannel('a');
                return $color->with(['r' => $r, 'g' => $g, 'b' => $b, 'a' => $a]);
            case CMYK::class:
                $c = (int)(max(0, min(100, $color->getChannel('c') - ($factor / 2.55))));
                $m = (int)(max(0, min(100, $color->getChannel('m') - ($factor / 2.55))));
                $y = (int)(max(0, min(100, $color->getChannel('y') - ($factor / 2.55))));
                $k = $color->getChannel('k');
                return $color->with(['c' => $c, 'm' => $m, 'y' => $y, 'k' => $k]);
            case HSL::class:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $h = $color->getChannel('h');
                $s = $color->getChannel('s');
                return $color->with(['h' => $h, 's' => $s, 'l' => $l]);
            case HSLA::class:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $h = $color->getChannel('h');
                $s = $color->getChannel('s');
                $a = $color->getChannel('a');
                return $color->with(['h' => $h, 's' => $s, 'l' => $l, 'a' => $a]);
            case HSV::class:
                $v = max(0, min(100, $color->getChannel('v') + $factor));
                $h = $color->getChannel('h');
                $s = $color->getChannel('s');
                return $color->with(['h' => $h, 's' => $s, 'v' => $v]);
            case LAB::class:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $a = $color->getChannel('a');
                $b = $color->getChannel('b');
                return $color->with(['l' => $l, 'a' => $a, 'b' => $b]);
            case LCh::class:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $c = $color->getChannel('c');
                $h = $color->getChannel('h');
                return $color->with(['l' => $l, 'c' => $c, 'h' => $h]);
            case XYZ::class:
                $x = max(0, min(100, $color->getChannel('x') + $factor));
                $y = max(0, min(100, $color->getChannel('y') + $factor));
                $z = max(0, min(100, $color->getChannel('z') + $factor));
                return $color->with(['x' => $x, 'y' => $y, 'z' => $z]);
            case YCBCR::class:
                $y = max(0, min(255, $color->getChannel('y') + $factor));
                $cb = $color->getChannel('cb');
                $cr = $color->getChannel('cr');
                return $color->with(['y' => $y, 'cb' => $cb, 'cr' => $cr]);
            default:
                throw new \InvalidArgumentException('Brightness filter not supported for color space: ' . $color->getColorSpaceName());
                break;
        }
    }
}

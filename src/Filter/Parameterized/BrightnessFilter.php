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
    public function getName(): string
    {
        return 'brightness';
    }

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
            case Lab::class:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $a = $color->getChannel('a');
                $b = $color->getChannel('b');
                return $color->with(['l' => (float)$l, 'a' => (float)$a, 'b' => (float)$b]);
            case LCh::class:
                $l = max(0, min(100, $color->getChannel('l') + $factor));
                $c = $color->getChannel('c');
                $h = $color->getChannel('h');
                return $color->with(['l' => (float)$l, 'c' => (float)$c, 'h' => (float)$h]);
            case XYZ::class:
                $x = max(0, min(100, $color->getChannel('x') + $factor));
                $y = max(0, min(100, $color->getChannel('y') + $factor));
                $z = max(0, min(100, $color->getChannel('z') + $factor));
                return $color->with(['x' => (float)$x, 'y' => (float)$y, 'z' => (float)$z]);
            case YCbCr::class:
                $y = max(0, min(255, $color->getChannel('y') + $factor));
                $cb = $color->getChannel('cb');
                $cr = $color->getChannel('cr');
                return $color->with(['y' => $y, 'cb' => $cb, 'cr' => $cr]);
            default:
                // Fallback: Convert to RGB, apply filter, convert back
                $originalColorSpace = $color->getColorSpace();
                $rgb = $color->toRGB();
                $r = max(0, min(255, $rgb->getChannel('r') + $factor));
                $g = max(0, min(255, $rgb->getChannel('g') + $factor));
                $b = max(0, min(255, $rgb->getChannel('b') + $factor));
                $rgbModified = $rgb->with(['r' => $r, 'g' => $g, 'b' => $b]);
                
                // Convert back to original color space
                $rgbValues = $rgbModified->toArray()['values'];
                
                // Get alpha if present
                $alpha = 255;
                $colorArray = $color->toArray();
                if (isset($colorArray['values']['a'])) {
                    $alpha = (int)$colorArray['values']['a'];
                }
                
                // Get CIE parameters if available
                $illuminant = null;
                $observer = null;
                if (method_exists($color, 'getIlluminant')) {
                    $illuminant = $color->getIlluminant();
                }
                if (method_exists($color, 'getObserver')) {
                    $observer = $color->getObserver();
                }
                
                $originalValues = $originalColorSpace::fromRGB(
                    $rgbValues,
                    $alpha,
                    $illuminant,
                    $observer
                );
                
                return $color->with($originalValues);
        }
    }
}

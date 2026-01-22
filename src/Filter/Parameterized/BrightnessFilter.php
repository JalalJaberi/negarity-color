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
                $r = max(0.0, min(255.0, $color->getChannelForCalculation('r') + $factor));
                $g = max(0.0, min(255.0, $color->getChannelForCalculation('g') + $factor));
                $b = max(0.0, min(255.0, $color->getChannelForCalculation('b') + $factor));
                return $color->with(['r' => $r, 'g' => $g, 'b' => $b]);
            case RGBA::class:
                $r = max(0.0, min(255.0, $color->getChannelForCalculation('r') + $factor));
                $g = max(0.0, min(255.0, $color->getChannelForCalculation('g') + $factor));
                $b = max(0.0, min(255.0, $color->getChannelForCalculation('b') + $factor));
                $a = $color->getChannelForCalculation('a');
                return $color->with(['r' => $r, 'g' => $g, 'b' => $b, 'a' => $a]);
            case CMYK::class:
                $c = max(0.0, min(100.0, $color->getChannelForCalculation('c') - ($factor / 2.55)));
                $m = max(0.0, min(100.0, $color->getChannelForCalculation('m') - ($factor / 2.55)));
                $y = max(0.0, min(100.0, $color->getChannelForCalculation('y') - ($factor / 2.55)));
                $k = $color->getChannelForCalculation('k');
                return $color->with(['c' => $c, 'm' => $m, 'y' => $y, 'k' => $k]);
            case HSL::class:
                $l = max(0.0, min(100.0, $color->getChannelForCalculation('l') + $factor));
                $h = $color->getChannelForCalculation('h');
                $s = $color->getChannelForCalculation('s');
                return $color->with(['h' => $h, 's' => $s, 'l' => $l]);
            case HSLA::class:
                $l = max(0.0, min(100.0, $color->getChannelForCalculation('l') + $factor));
                $h = $color->getChannelForCalculation('h');
                $s = $color->getChannelForCalculation('s');
                $a = $color->getChannelForCalculation('a');
                return $color->with(['h' => $h, 's' => $s, 'l' => $l, 'a' => $a]);
            case HSV::class:
                $v = max(0.0, min(100.0, $color->getChannelForCalculation('v') + $factor));
                $h = $color->getChannelForCalculation('h');
                $s = $color->getChannelForCalculation('s');
                return $color->with(['h' => $h, 's' => $s, 'v' => $v]);
            case Lab::class:
                $l = max(0.0, min(100.0, $color->getChannelForCalculation('l') + $factor));
                $a = $color->getChannelForCalculation('a');
                $b = $color->getChannelForCalculation('b');
                return $color->with(['l' => $l, 'a' => $a, 'b' => $b]);
            case LCh::class:
                $l = max(0.0, min(100.0, $color->getChannelForCalculation('l') + $factor));
                $c = $color->getChannelForCalculation('c');
                $h = $color->getChannelForCalculation('h');
                return $color->with(['l' => $l, 'c' => $c, 'h' => $h]);
            case XYZ::class:
                $x = max(0.0, min(100.0, $color->getChannelForCalculation('x') + $factor));
                $y = max(0.0, min(100.0, $color->getChannelForCalculation('y') + $factor));
                $z = max(0.0, min(100.0, $color->getChannelForCalculation('z') + $factor));
                return $color->with(['x' => $x, 'y' => $y, 'z' => $z]);
            case YCbCr::class:
                $y = max(0.0, min(255.0, $color->getChannelForCalculation('y') + $factor));
                $cb = $color->getChannelForCalculation('cb');
                $cr = $color->getChannelForCalculation('cr');
                return $color->with(['y' => $y, 'cb' => $cb, 'cr' => $cr]);
            default:
                // Fallback: Convert to RGB, apply filter, convert back
                $originalColorSpace = $color->getColorSpace();
                $rgb = $color->toRGB();
                $r = max(0.0, min(255.0, $rgb->getChannelForCalculation('r') + $factor));
                $g = max(0.0, min(255.0, $rgb->getChannelForCalculation('g') + $factor));
                $b = max(0.0, min(255.0, $rgb->getChannelForCalculation('b') + $factor));
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

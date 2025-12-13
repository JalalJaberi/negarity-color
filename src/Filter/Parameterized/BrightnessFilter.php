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
            default:
                throw new \InvalidArgumentException('Brightness filter not supported for color space: ' . $color->getColorSpace()->getName());
                break;
        }
    }
}

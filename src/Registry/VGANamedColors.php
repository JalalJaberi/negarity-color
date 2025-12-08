<?php

declare(strict_types=1);

namespace Negarity\Color\Registry;

use Negarity\Color\ColorSpace\{
    ColorSpaceEnum,
    ColorSpaceInterface,
    RGB
};

class VGANamedColors implements NamedColorRegistryInterface
{
    private array $colors = [
        'white' => [
            ColorSpaceEnum::RGB->value => [255, 255, 255],
            ColorSpaceEnum::RGBA->value => [255, 255, 255, 255],
            ColorSpaceEnum::CMYK->value => [0, 0, 0, 0],
            ColorSpaceEnum::HSL->value => [0, 0, 100],
            ColorSpaceEnum::HSLA->value => [0, 0, 100, 1.0],
            ColorSpaceEnum::HSV->value => [0, 0, 100],
            ColorSpaceEnum::LAB->value => [100.00, 0.005, -0.010],
            ColorSpaceEnum::LCH->value => [100.00, 0.011, 272.06],
            ColorSpaceEnum::XYZ->value => [95.05, 100.00, 108.88],
            ColorSpaceEnum::YCBCR->value => [235.0, 128.0, 128.0],
        ],
        'silver' => [
            ColorSpaceEnum::RGB->value => [192, 192, 192],
            ColorSpaceEnum::RGBA->value => [192, 192, 192, 255],
            ColorSpaceEnum::CMYK->value => [0, 0, 0, 25],
            ColorSpaceEnum::HSL->value => [0, 0, 75],
            ColorSpaceEnum::HSLA->value => [0, 0, 75, 1.0],
            ColorSpaceEnum::HSV->value => [0, 0, 75],
            ColorSpaceEnum::LAB->value => [78.28, 0.000, -0.000],
            ColorSpaceEnum::LCH->value => [78.28, 0.000, 0.00],
            ColorSpaceEnum::XYZ->value => [77.00, 81.59, 87.12],
            ColorSpaceEnum::YCBCR->value => [188.976, 128.000, 128.000],
        ],
        'gray' => [
            ColorSpaceEnum::RGB->value => [128, 128, 128],
            ColorSpaceEnum::RGBA->value => [128, 128, 128, 255],
            ColorSpaceEnum::CMYK->value => [0, 0, 0, 50],
            ColorSpaceEnum::HSL->value => [0, 0, 50],
            ColorSpaceEnum::HSLA->value => [0, 0, 50, 1.0],
            ColorSpaceEnum::HSV->value => [0, 0, 50],
            ColorSpaceEnum::LAB->value => [53.59, 0.000, -0.000],
            ColorSpaceEnum::LCH->value => [53.59, 0.000, 0.00],
            ColorSpaceEnum::XYZ->value => [41.24, 43.06, 46.29],
            ColorSpaceEnum::YCBCR->value => [105.537, 128.000, 128.000],
        ],
        'black' => [
            ColorSpaceEnum::RGB->value => [0, 0, 0],
            ColorSpaceEnum::RGBA->value => [0, 0, 0, 255],
            ColorSpaceEnum::CMYK->value => [0, 0, 0, 100],
            ColorSpaceEnum::HSL->value => [0, 0, 0],
            ColorSpaceEnum::HSLA->value => [0, 0, 0, 1.0],
            ColorSpaceEnum::HSV->value => [0, 0, 0],
            ColorSpaceEnum::LAB->value => [0.00, 0.000, -0.000],
            ColorSpaceEnum::LCH->value => [0.00, 0.000, 0.00],
            ColorSpaceEnum::XYZ->value => [0.00, 0.00, 0.00],
            ColorSpaceEnum::YCBCR->value => [16.000, 128.000, 128.000],
        ],
        'red' => [
            ColorSpaceEnum::RGB->value => [255, 0, 0],
            ColorSpaceEnum::RGBA->value => [255, 0, 0, 255],
            ColorSpaceEnum::CMYK->value => [0, 100, 100, 0],
            ColorSpaceEnum::HSL->value => [0, 100, 50],
            ColorSpaceEnum::HSLA->value => [0, 100, 50, 1.0],
            ColorSpaceEnum::HSV->value => [0, 100, 100],
            ColorSpaceEnum::LAB->value => [53.24, 80.09, 67.20],
            ColorSpaceEnum::LCH->value => [53.24, 104.55, 39.99],
            ColorSpaceEnum::XYZ->value => [41.24, 21.26, 1.93],
            ColorSpaceEnum::YCBCR->value => [76.245, 84.972, 255.0],
        ],
        'maroon' => [
            ColorSpaceEnum::RGB->value => [128, 0, 0],
            ColorSpaceEnum::RGBA->value => [128, 0, 0, 255],
            ColorSpaceEnum::CMYK->value => [0, 100, 100, 50],
            ColorSpaceEnum::HSL->value => [0, 100, 25],
            ColorSpaceEnum::HSLA->value => [0, 100, 25, 1.0],
            ColorSpaceEnum::HSV->value => [0, 100, 50],
            ColorSpaceEnum::LAB->value => [25.53, 53.23, 44.22],
            ColorSpaceEnum::LCH->value => [25.53, 69.05, 39.99],
            ColorSpaceEnum::XYZ->value => [14.03, 7.23, 0.66],
            ColorSpaceEnum::YCBCR->value => [37.753, 84.972, 255.0],
        ],
        'yellow' => [
            ColorSpaceEnum::RGB->value => [255, 255, 0],
            ColorSpaceEnum::RGBA->value => [255, 255, 0, 255],
            ColorSpaceEnum::CMYK->value => [0, 0, 100, 0],
            ColorSpaceEnum::HSL->value => [60, 100, 50],
            ColorSpaceEnum::HSLA->value => [60, 100, 50, 1.0],
            ColorSpaceEnum::HSV->value => [60, 100, 100],
            ColorSpaceEnum::LAB->value => [97.14, -21.55, 94.48],
            ColorSpaceEnum::LCH->value => [97.14, 96.03, 102.88],
            ColorSpaceEnum::XYZ->value => [77.00, 92.78, 13.85],
            ColorSpaceEnum::YCBCR->value => [225.93, 0.000, 148.130],
        ],
        'olive' => [
            ColorSpaceEnum::RGB->value => [128, 128, 0],
            ColorSpaceEnum::RGBA->value => [128, 128, 0, 255],
            ColorSpaceEnum::CMYK->value => [0, 0, 100, 50],
            ColorSpaceEnum::HSL->value => [60, 100, 25],
            ColorSpaceEnum::HSLA->value => [60, 100, 25, 1.0],
            ColorSpaceEnum::HSV->value => [60, 100, 50],
            ColorSpaceEnum::LAB->value => [50.00, -12.88, 56.55],
            ColorSpaceEnum::LCH->value => [50.00, 58.02, 102.88],
            ColorSpaceEnum::XYZ->value => [29.01, 34.11, 5.09],
            ColorSpaceEnum::YCBCR->value => [112.966, 0.000, 148.130],
        ],
        'lime' => [
            ColorSpaceEnum::RGB->value => [0, 255, 0],
            ColorSpaceEnum::RGBA->value => [0, 255, 0, 255],
            ColorSpaceEnum::CMYK->value => [100, 0, 100, 0],
            ColorSpaceEnum::HSL->value => [120, 100, 50],
            ColorSpaceEnum::HSLA->value => [120, 100, 50, 1.0],
            ColorSpaceEnum::HSV->value => [120, 100, 100],
            ColorSpaceEnum::LAB->value => [87.74, -86.18, 83.18],
            ColorSpaceEnum::LCH->value => [87.74, 119.24, 136.02],
            ColorSpaceEnum::XYZ->value => [35.76, 71.52, 11.92],
            ColorSpaceEnum::YCBCR->value => [149.685, 43.527, 21.234],
        ],
        'green' => [
            ColorSpaceEnum::RGB->value => [0, 128, 0],
            ColorSpaceEnum::RGBA->value => [0, 128, 0, 255],
            ColorSpaceEnum::CMYK->value => [100, 0, 100, 50],
            ColorSpaceEnum::HSL->value => [120, 100, 25],
            ColorSpaceEnum::HSLA->value => [120, 100, 25, 1.0],
            ColorSpaceEnum::HSV->value => [120, 100, 50],
            ColorSpaceEnum::LAB->value => [46.23, -51.70, 49.90],
            ColorSpaceEnum::LCH->value => [46.23, 71.03, 136.02],
            ColorSpaceEnum::XYZ->value => [15.04, 30.06, 5.01],
            ColorSpaceEnum::YCBCR->value => [75.842, 43.527, 21.234],
        ],
        'aqua' => [
            ColorSpaceEnum::RGB->value => [0, 255, 255],
            ColorSpaceEnum::RGBA->value => [0, 255, 255, 255],
            ColorSpaceEnum::CMYK->value => [100, 0, 0, 0],
            ColorSpaceEnum::HSL->value => [180, 100, 50],
            ColorSpaceEnum::HSLA->value => [180, 100, 50, 1.0],
            ColorSpaceEnum::HSV->value => [180, 100, 100],
            ColorSpaceEnum::LAB->value => [91.12, -48.08, -14.14],
            ColorSpaceEnum::LCH->value => [91.12, 50.02, 196.38],
            ColorSpaceEnum::XYZ->value => [53.81, 78.74, 106.97],
            ColorSpaceEnum::YCBCR->value => [188.976, 173.813, 0.000],
        ],
        'teal' => [
            ColorSpaceEnum::RGB->value => [0, 128, 128],
            ColorSpaceEnum::RGBA->value => [0, 128, 128, 255],
            ColorSpaceEnum::CMYK->value => [100, 0, 0, 50],
            ColorSpaceEnum::HSL->value => [180, 100, 25],
            ColorSpaceEnum::HSLA->value => [180, 100, 25, 1.0],
            ColorSpaceEnum::HSV->value => [180, 100, 50],
            ColorSpaceEnum::LAB->value => [48.30, -26.03, -7.64],
            ColorSpaceEnum::LCH->value => [48.30, 27.01, 196.38],
            ColorSpaceEnum::XYZ->value => [22.40, 32.37, 44.08],
            ColorSpaceEnum::YCBCR->value => [94.488, 173.813, 0.000],
        ],
        'blue' => [
            ColorSpaceEnum::RGB->value => [0, 0, 255],
            ColorSpaceEnum::RGBA->value => [0, 0, 255, 255],
            ColorSpaceEnum::CMYK->value => [100, 100, 0, 0],
            ColorSpaceEnum::HSL->value => [240, 100, 50],
            ColorSpaceEnum::HSLA->value => [240, 100, 50, 1.0],
            ColorSpaceEnum::HSV->value => [240, 100, 100],
            ColorSpaceEnum::LAB->value => [32.30, 79.19, -107.86],
            ColorSpaceEnum::LCH->value => [32.30, 134.18, 306.29],
            ColorSpaceEnum::XYZ->value => [18.05, 7.22, 95.05],
            ColorSpaceEnum::YCBCR->value => [41.243, 0.000, 255.0],
        ],
        'navy' => [
            ColorSpaceEnum::RGB->value => [0, 0, 128],
            ColorSpaceEnum::RGBA->value => [0, 0, 128, 255],
            ColorSpaceEnum::CMYK->value => [100, 100, 0, 50],
            ColorSpaceEnum::HSL->value => [240, 100, 25],
            ColorSpaceEnum::HSLA->value => [240, 100, 25, 1.0],
            ColorSpaceEnum::HSV->value => [240, 100, 50],
            ColorSpaceEnum::LAB->value => [14.04, 34.32, -46.14],
            ColorSpaceEnum::LCH->value => [14.04, 57.62, 306.29],
            ColorSpaceEnum::XYZ->value => [7.31, 2.92, 40.05],
            ColorSpaceEnum::YCBCR->value => [20.621, 0.000, 255.0],
        ],
        'fuchsia' => [
            ColorSpaceEnum::RGB->value => [255, 0, 255],
            ColorSpaceEnum::RGBA->value => [255, 0, 255, 255],
            ColorSpaceEnum::CMYK->value => [0, 100, 0, 0],
            ColorSpaceEnum::HSL->value => [300, 100, 50],
            ColorSpaceEnum::HSLA->value => [300, 100, 50, 1.0],
            ColorSpaceEnum::HSV->value => [300, 100, 100],
            ColorSpaceEnum::LAB->value => [60.32, 98.25, -60.84],
            ColorSpaceEnum::LCH->value => [60.32, 116.03, 329.01],
            ColorSpaceEnum::XYZ->value => [59.29, 28.48, 96.99],
            ColorSpaceEnum::YCBCR->value => [105.537, 212.000, 0.000],
        ],
        'purple' => [
            ColorSpaceEnum::RGB->value => [128, 0, 128],
            ColorSpaceEnum::RGBA->value => [128, 0, 128, 255],
            ColorSpaceEnum::CMYK->value => [0, 100, 0, 50],
            ColorSpaceEnum::HSL->value => [300, 100, 25],
            ColorSpaceEnum::HSLA->value => [300, 100, 25, 1.0],
            ColorSpaceEnum::HSV->value => [300, 100, 50],
            ColorSpaceEnum::LAB->value => [29.78, 48.98, -30.19],
            ColorSpaceEnum::LCH->value => [29.78, 56.03, 329.01],
            ColorSpaceEnum::XYZ->value => [23.15, 11.11, 40.05],
            ColorSpaceEnum::YCBCR->value => [52.769, 212.000, 0.000],
        ],
    ];

    public function has(string $colorName, string $colorSpaceName): bool
    {
        return isset($this->colors[$colorName][$colorSpaceName]);
    }

    public function getColorByName(string $colorName, string $colorSpaceName): ColorSpaceInterface
    {
        $values = $this->colors[$colorName][$colorSpaceName];
        switch ($colorSpaceName) {
            case ColorSpaceEnum::RGB->value:
                return new RGB(...$values);
        }
    }
}

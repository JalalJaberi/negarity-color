<?php

declare(strict_types=1);

namespace Negarity\Color\Registry;

use Negarity\Color\ColorSpace\{
    ColorSpaceInterface,
    RGB,
    RGBA,
    CMYK,
    HSL,
    HSLA,
    HSV,
    LAB,
    LCH,
    XYZ,
    YCBCR
};
use Negarity\Color\Exception\NamedColorNotFoundException;

class VGANamedColors implements NamedColorRegistryInterface
{
    /**
     * @var array<string, array<class-string<ColorSpaceInterface>, array<string, float|int>>>
    */
    private array $colors = [
        'white' => [
            RGB::class => ['r' => 255, 'g' => 255, 'b' => 255],
            RGBA::class => ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 0, 'y' => 0, 'k' => 0],
            HSL::class => ['h' => 0, 's' => 0, 'l' => 100],
            HSLA::class => ['h' => 0, 's' => 0, 'l' => 100, 'a' => 255],
            HSV::class => ['s' => 0, 's' => 0, 'v' => 100],
            LAB::class => ['l' => 100.00, 'a' => 0.005, 'b' => -0.010],
            LCH::class => ['l' => 100.00, 'c' => 0.011, 'h' => 272.06],
            XYZ::class => ['x' => 95.05, 'y' => 100.00, 'z' => 108.88],
            YCBCR::class => ['y' => 235.0, 'cb' => 128.0, 'cr' => 128.0],
        ],
        'silver' => [
            RGB::class => ['r' => 192, 'g' => 192, 'b' => 192],
            RGBA::class => ['r' => 192, 'g' => 192, 'b' => 192, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 0, 'y' => 0, 'k' => 25],
            HSL::class => ['h' => 0, 's' => 0, 'l' => 75],
            HSLA::class => ['h' => 0, 's' => 0, 'l' => 75, 'a' => 255],
            HSV::class => ['s' => 0, 's' => 0, 'v' => 75],
            LAB::class => ['l' => 78.28, 'a' => 0.000, 'b' => -0.000],
            LCH::class => ['l' => 78.28, 'c' => 0.000, 'h' => 0.00],
            XYZ::class => ['x' => 77.00, 'y' => 81.59, 'z' => 87.12],
            YCBCR::class => ['y' => 188.976, 'cb' => 128.000, 'cr' => 128.000],
        ],
        'gray' => [
            RGB::class => ['r' => 128, 'g' => 128, 'b' => 128],
            RGBA::class => ['r' => 128, 'g' => 128, 'b' => 128, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 0, 'y' => 0, 'k' => 50],
            HSL::class => ['h' => 0, 's' => 0, 'l' => 50],
            HSLA::class => ['h' => 0, 's' => 0, 'l' => 50, 'a' => 255],
            HSV::class => ['s' => 0, 's' => 0, 'v' => 50],
            LAB::class => ['l' => 53.59, 'a' => 0.000, 'b' => -0.000],
            LCH::class => ['l' => 53.59, 'c' => 0.000, 'h' => 0.00],
            XYZ::class => ['x' => 41.24, 'y' => 43.06, 'z' => 46.29],
            YCBCR::class => ['y' => 105.537, 'cb' => 128.000, 'cr' => 128.000],
        ],
        'black' => [
            RGB::class => ['r' => 0, 'g' => 0, 'b' => 0],
            RGBA::class => ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 0, 'y' => 0, 'k' => 100],
            HSL::class => ['h' => 0, 's' => 0, 'l' => 0],
            HSLA::class => ['h' => 0, 's' => 0, 'l' => 0, 'a' => 255],
            HSV::class => ['s' => 0, 's' => 0, 'v' => 0],
            LAB::class => ['l' => 0.00, 'a' => 0.000, 'b' => -0.000],
            LCH::class => ['l' => 0.00, 'c' => 0.000, 'h' => 0.00],
            XYZ::class => ['x' => 0.00, 'y' => 0.00, 'z' => 0.00],
            YCBCR::class => ['y' => 16.000, 'cb' => 128.000, 'cr' => 128.000],
        ],
        'red' => [
            RGB::class => ['r' => 255, 'g' => 0, 'b' => 0],
            RGBA::class => ['r' => 255, 'g' => 0, 'b' => 0, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 100, 'y' => 100, 'k' => 0],
            HSL::class => ['h' => 0, 's' => 100, 'l' => 50],
            HSLA::class => ['h' => 0, 's' => 100, 'l' => 50, 'a' => 255],
            HSV::class => ['s' => 0, 's' => 100, 'v' => 100],
            LAB::class => ['l' => 53.24, 'a' => 80.09, 'b' => 67.20],
            LCH::class => ['l' => 53.24, 'c' => 104.55, 'h' => 39.99],
            XYZ::class => ['x' => 41.24, 'y' => 21.26, 'z' => 1.93],
            YCBCR::class => ['y' => 76.245, 'cb' => 84.972, 'cr' => 255.0],
        ],
        'maroon' => [
            RGB::class => ['r' => 128, 'g' => 0, 'b' => 0],
            RGBA::class => ['r' => 128, 'g' => 0, 'b' => 0, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 100, 'y' => 100, 'k' => 50],
            HSL::class => ['h' => 0, 's' => 100, 'l' => 25],
            HSLA::class => ['h' => 0, 's' => 100, 'l' => 25, 'a' => 255],
            HSV::class => ['s' => 0, 's' => 100, 'v' => 50],
            LAB::class => ['l' => 25.53, 'a' => 53.23, 'b' => 44.22],
            LCH::class => ['l' => 25.53, 'c' => 69.05, 'h' => 39.99],
            XYZ::class => ['x' => 14.03, 'y' => 7.23, 'z' => 0.66],
            YCBCR::class => ['y' => 37.753, 'cb' => 84.972, 'cr' => 255.0],
        ],
        'yellow' => [
            RGB::class => ['r' => 255, 'g' => 255, 'b' => 0],
            RGBA::class => ['r' => 255, 'g' => 255, 'b' => 0, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 0, 'y' => 100, 'k' => 0],
            HSL::class => ['h' => 60, 's' => 100, 'l' => 50],
            HSLA::class => ['h' => 60, 's' => 100, 'l' => 50, 'a' => 255],
            HSV::class => ['s' => 60, 's' => 100, 'v' => 100],
            LAB::class => ['l' => 97.14, 'a' => -21.55, 'b' => 94.48],
            LCH::class => ['l' => 97.14, 'c' => 96.03, 'h' => 102.88],
            XYZ::class => ['x' => 77.00, 'y' => 92.78, 'z' => 13.85],
            YCBCR::class => ['y' => 225.93, 'cb' => 0.000, 'cr' => 148.130],
        ],
        'olive' => [
            RGB::class => ['r' => 128, 'g' => 128, 'b' => 0],
            RGBA::class => ['r' => 128, 'g' => 128, 'b' => 0, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 0, 'y' => 100, 'k' => 50],
            HSL::class => ['h' => 60, 's' => 100, 'l' => 25],
            HSLA::class => ['h' => 60, 's' => 100, 'l' => 25, 'a' => 255],
            HSV::class => ['s' => 60, 's' => 100, 'v' => 50],
            LAB::class => ['l' => 50.00, 'a' => -12.88, 'b' => 56.55],
            LCH::class => ['l' => 50.00, 'c' => 58.02, 'h' => 102.88],
            XYZ::class => ['x' => 29.01, 'y' => 34.11, 'z' => 5.09],
            YCBCR::class => ['y' => 112.966, 'cb' => 0.000, 'cr' => 148.130],
        ],
        'lime' => [
            RGB::class => ['r' => 0, 'g' => 255, 'b' => 0],
            RGBA::class => ['r' => 0, 'g' => 255, 'b' => 0, 'a' => 255],
            CMYK::class => ['c' =>100, 'm' => 0, 'y' => 100, 'k' => 0],
            HSL::class => ['h' => 120, 's' => 100, 'l' => 50],
            HSLA::class => ['h' => 120, 's' => 100, 'l' => 50, 'a' => 255],
            HSV::class => ['s' => 120, 's' => 100, 'v' => 100],
            LAB::class => ['l' => 87.74, 'a' => -86.18, 'b' => 83.18],
            LCH::class => ['l' => 87.74, 'c' => 119.24, 'h' => 136.02],
            XYZ::class => ['x' => 35.76, 'y' => 71.52, 'z' => 11.92],
            YCBCR::class => ['y' => 149.685, 'cb' => 43.527, 'cr' => 21.234],
        ],
        'green' => [
            RGB::class => ['r' => 0, 'g' => 128, 'b' => 0],
            RGBA::class => ['r' => 0, 'g' => 128, 'b' => 0, 'a' => 255],
            CMYK::class => ['c' =>100, 'm' => 0, 'y' => 100, 'k' => 50],
            HSL::class => ['h' => 120, 's' => 100, 'l' => 25],
            HSLA::class => ['h' => 120, 's' => 100, 'l' => 25, 'a' => 255],
            HSV::class => ['s' => 120, 's' => 100, 'v' => 50],
            LAB::class => ['l' => 46.23, 'a' => -51.70, 'b' => 49.90],
            LCH::class => ['l' => 46.23, 'c' => 71.03, 'h' => 136.02],
            XYZ::class => ['x' => 15.04, 'y' => 30.06, 'z' => 5.01],
            YCBCR::class => ['y' => 75.842, 'cb' => 43.527, 'cr' => 21.234],
        ],
        'aqua' => [
            RGB::class => ['r' => 0, 'g' => 255, 'b' => 255],
            RGBA::class => ['r' => 0, 'g' => 255, 'b' => 255, 'a' => 255],
            CMYK::class => ['c' =>100, 'm' => 0, 'y' => 0, 'k' => 0],
            HSL::class => ['h' => 180, 's' => 100, 'l' => 50],
            HSLA::class => ['h' => 180, 's' => 100, 'l' => 50, 'a' => 255],
            HSV::class => ['s' => 180, 's' => 100, 'v' => 100],
            LAB::class => ['l' => 91.12, 'a' => -48.08, 'b' => -14.14],
            LCH::class => ['l' => 91.12, 'c' => 50.02, 'h' => 196.38],
            XYZ::class => ['x' => 53.81, 'y' => 78.74, 'z' => 106.97],
            YCBCR::class => ['y' => 188.976, 'cb' => 173.813, 'cr' => 0.000],
        ],
        'teal' => [
            RGB::class => ['r' => 0, 'g' => 128, 'b' => 128],
            RGBA::class => ['r' => 0, 'g' => 128, 'b' => 128, 'a' => 255],
            CMYK::class => ['c' =>100, 'm' => 0, 'y' => 0, 'k' => 50],
            HSL::class => ['h' => 180, 's' => 100, 'l' => 25],
            HSLA::class => ['h' => 180, 's' => 100, 'l' => 25, 'a' => 255],
            HSV::class => ['s' => 180, 's' => 100, 'v' => 50],
            LAB::class => ['l' => 48.30, 'a' => -26.03, 'b' => -7.64],
            LCH::class => ['l' => 48.30, 'c' => 27.01, 'h' => 196.38],
            XYZ::class => ['x' => 22.40, 'y' => 32.37, 'z' => 44.08],
            YCBCR::class => ['y' => 94.488, 'cb' => 173.813, 'cr' => 0.000],
        ],
        'blue' => [
            RGB::class => ['r' => 0, 'g' => 0, 'b' => 255],
            RGBA::class => ['r' => 0, 'g' => 0, 'b' => 255, 'a' => 255],
            CMYK::class => ['c' =>100, 'm' => 100, 'y' => 0, 'k' => 0],
            HSL::class => ['h' => 240, 's' => 100, 'l' => 50],
            HSLA::class => ['h' => 240, 's' => 100, 'l' => 50, 'a' => 255],
            HSV::class => ['s' => 240, 's' => 100, 'v' => 100],
            LAB::class => ['l' => 32.30, 'a' => 79.19, 'b' => -107.86],
            LCH::class => ['l' => 32.30, 'c' => 134.18, 'h' => 306.29],
            XYZ::class => ['x' => 18.05, 'y' => 7.22, 'z' => 95.05],
            YCBCR::class => ['y' => 41.243, 'cb' => 0.000, 'cr' => 255.0],
        ],
        'navy' => [
            RGB::class => ['r' => 0, 'g' => 0, 'b' => 128],
            RGBA::class => ['r' => 0, 'g' => 0, 'b' => 128, 'a' => 255],
            CMYK::class => ['c' =>100, 'm' => 100, 'y' => 0, 'k' => 50],
            HSL::class => ['h' => 240, 's' => 100, 'l' => 25],
            HSLA::class => ['h' => 240, 's' => 100, 'l' => 25, 'a' => 255],
            HSV::class => ['s' => 240, 's' => 100, 'v' => 50],
            LAB::class => ['l' => 14.04, 'a' => 34.32, 'b' => -46.14],
            LCH::class => ['l' => 14.04, 'c' => 57.62, 'h' => 306.29],
            XYZ::class => ['x' => 7.31, 'y' => 2.92, 'z' => 40.05],
            YCBCR::class => ['y' => 20.621, 'cb' => 0.000, 'cr' => 255.0],
        ],
        'fuchsia' => [
            RGB::class => ['r' => 255, 'g' => 0, 'b' => 255],
            RGBA::class => ['r' => 255, 'g' => 0, 'b' => 255, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 100, 'y' => 0, 'k' => 0],
            HSL::class => ['h' => 300, 's' => 100, 'l' => 50],
            HSLA::class => ['h' => 300, 's' => 100, 'l' => 50, 'a' => 255],
            HSV::class => ['s' => 300, 's' => 100, 'v' => 100],
            LAB::class => ['l' => 60.32, 'a' => 98.25, 'b' => -60.84],
            LCH::class => ['l' => 60.32, 'c' => 116.03, 'h' => 329.01],
            XYZ::class => ['x' => 59.29, 'y' => 28.48, 'z' => 96.99],
            YCBCR::class => ['y' => 105.537, 'cb' => 212.000, 'cr' => 0.000],
        ],
        'purple' => [
            RGB::class => ['r' => 128, 'g' => 0, 'b' => 128],
            RGBA::class => ['r' => 128, 'g' => 0, 'b' => 128, 'a' => 255],
            CMYK::class => ['c' =>0, 'm' => 100, 'y' => 0, 'k' => 50],
            HSL::class => ['h' => 300, 's' => 100, 'l' => 25],
            HSLA::class => ['h' => 300, 's' => 100, 'l' => 25, 'a' => 255],
            HSV::class => ['s' => 300, 's' => 100, 'v' => 50],
            LAB::class => ['l' => 29.78, 'a' => 48.98, 'b' => -30.19],
            LCH::class => ['l' => 29.78, 'c' => 56.03, 'h' => 329.01],
            XYZ::class => ['x' => 23.15, 'y' => 11.11, 'z' => 40.05],
            YCBCR::class => ['y' => 52.769, 'cb' => 212.000, 'cr' => 0.000],
        ],
    ];

    #[\Override]
    public function has(string $colorName, string $colorSpace): bool
    {
        return isset($this->colors[$colorName][$colorSpace]);
    }

    #[\Override]
    public function getColorValuesByName(string $colorName, string $colorSpace): array
    {
        if (!isset($this->colors[$colorName])) {
            throw new NamedColorNotFoundException(
                "Named color '{$colorName}' not found in registry."
            );
        }
        
        if (!isset($this->colors[$colorName][$colorSpace])) {
            throw new NamedColorNotFoundException(
                "Named color '{$colorName}' does not have values for color space '{$colorSpace}'."
            );
        }
        
        return $this->colors[$colorName][$colorSpace];
    }
}

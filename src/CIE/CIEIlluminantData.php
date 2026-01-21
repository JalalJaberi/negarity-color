<?php

declare(strict_types=1);

namespace Negarity\Color\CIE;

/**
 * CIE Standard Illuminant XYZ Tristimulus Values.
 * 
 * Contains the XYZ tristimulus values for all standard illuminants
 * for both 2° and 10° standard observers.
 * 
 * Values are normalized so Y = 100 (or 1.0 in some conventions).
 * 
 * @see https://en.wikipedia.org/wiki/Standard_illuminant
 */
final class CIEIlluminantData
{
    /**
     * Get XYZ tristimulus values for a given illuminant and observer.
     * 
     * @param CIEIlluminant $illuminant The illuminant
     * @param CIEObserver $observer The observer (2° or 10°)
     * @return array{x: float, y: float, z: float} XYZ values (Y normalized to 100)
     */
    public static function getXYZ(CIEIlluminant $illuminant, CIEObserver $observer): array
    {
        return match ($observer) {
            CIEObserver::TwoDegree => self::getTwoDegreeXYZ($illuminant),
            CIEObserver::TenDegree => self::getTenDegreeXYZ($illuminant),
        };
    }

    /**
     * Get XYZ tristimulus values for 2° standard observer (CIE 1931).
     * 
     * @param CIEIlluminant $illuminant
     * @return array{x: float, y: float, z: float}
     */
    private static function getTwoDegreeXYZ(CIEIlluminant $illuminant): array
    {
        return match ($illuminant) {
            CIEIlluminant::A => ['x' => 109.850, 'y' => 100.000, 'z' => 35.585],
            CIEIlluminant::B => ['x' => 99.0927, 'y' => 100.000, 'z' => 85.313],
            CIEIlluminant::C => ['x' => 98.074, 'y' => 100.000, 'z' => 118.232],
            CIEIlluminant::D50 => ['x' => 96.422, 'y' => 100.000, 'z' => 82.521],
            CIEIlluminant::D55 => ['x' => 95.682, 'y' => 100.000, 'z' => 92.149],
            CIEIlluminant::D65 => ['x' => 95.047, 'y' => 100.000, 'z' => 108.883],
            CIEIlluminant::D75 => ['x' => 94.972, 'y' => 100.000, 'z' => 122.638],
            CIEIlluminant::E => ['x' => 100.000, 'y' => 100.000, 'z' => 100.000],
            CIEIlluminant::F1 => ['x' => 92.834, 'y' => 100.000, 'z' => 103.665],
            CIEIlluminant::F2 => ['x' => 99.187, 'y' => 100.000, 'z' => 67.395],
            CIEIlluminant::F3 => ['x' => 103.754, 'y' => 100.000, 'z' => 49.861],
            CIEIlluminant::F4 => ['x' => 109.147, 'y' => 100.000, 'z' => 38.813],
            CIEIlluminant::F5 => ['x' => 90.872, 'y' => 100.000, 'z' => 98.723],
            CIEIlluminant::F6 => ['x' => 97.309, 'y' => 100.000, 'z' => 60.191],
            CIEIlluminant::F7 => ['x' => 95.044, 'y' => 100.000, 'z' => 108.755],
            CIEIlluminant::F8 => ['x' => 96.413, 'y' => 100.000, 'z' => 82.333],
            CIEIlluminant::F9 => ['x' => 100.365, 'y' => 100.000, 'z' => 67.868],
            CIEIlluminant::F10 => ['x' => 96.174, 'y' => 100.000, 'z' => 81.712],
            CIEIlluminant::F11 => ['x' => 100.966, 'y' => 100.000, 'z' => 64.370],
            CIEIlluminant::F12 => ['x' => 108.046, 'y' => 100.000, 'z' => 39.228],
        };
    }

    /**
     * Get XYZ tristimulus values for 10° standard observer (CIE 1964).
     * 
     * @param CIEIlluminant $illuminant
     * @return array{x: float, y: float, z: float}
     */
    private static function getTenDegreeXYZ(CIEIlluminant $illuminant): array
    {
        return match ($illuminant) {
            CIEIlluminant::A => ['x' => 111.144, 'y' => 100.000, 'z' => 35.200],
            CIEIlluminant::B => ['x' => 99.178, 'y' => 100.000, 'z' => 84.349],
            CIEIlluminant::C => ['x' => 97.285, 'y' => 100.000, 'z' => 116.145],
            CIEIlluminant::D50 => ['x' => 96.720, 'y' => 100.000, 'z' => 81.427],
            CIEIlluminant::D55 => ['x' => 95.799, 'y' => 100.000, 'z' => 90.926],
            CIEIlluminant::D65 => ['x' => 94.811, 'y' => 100.000, 'z' => 107.304],
            CIEIlluminant::D75 => ['x' => 94.416, 'y' => 100.000, 'z' => 120.641],
            CIEIlluminant::E => ['x' => 100.000, 'y' => 100.000, 'z' => 100.000],
            CIEIlluminant::F1 => ['x' => 94.791, 'y' => 100.000, 'z' => 103.191],
            CIEIlluminant::F2 => ['x' => 103.280, 'y' => 100.000, 'z' => 69.026],
            CIEIlluminant::F3 => ['x' => 108.968, 'y' => 100.000, 'z' => 51.965],
            CIEIlluminant::F4 => ['x' => 114.961, 'y' => 100.000, 'z' => 40.963],
            CIEIlluminant::F5 => ['x' => 93.369, 'y' => 100.000, 'z' => 98.636],
            CIEIlluminant::F6 => ['x' => 102.148, 'y' => 100.000, 'z' => 62.074],
            CIEIlluminant::F7 => ['x' => 95.792, 'y' => 100.000, 'z' => 107.687],
            CIEIlluminant::F8 => ['x' => 97.115, 'y' => 100.000, 'z' => 81.135],
            CIEIlluminant::F9 => ['x' => 102.116, 'y' => 100.000, 'z' => 67.826],
            CIEIlluminant::F10 => ['x' => 99.001, 'y' => 100.000, 'z' => 83.134],
            CIEIlluminant::F11 => ['x' => 103.866, 'y' => 100.000, 'z' => 65.627],
            CIEIlluminant::F12 => ['x' => 111.428, 'y' => 100.000, 'z' => 40.353],
        };
    }
}

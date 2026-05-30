<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEIlluminantData;
use Negarity\Color\CIE\CIEObserver;
use Negarity\Color\CIE\CieCamAppearance;
use Negarity\Color\ColorInterface;

/**
 * Extracts display “brightness” (not physical luminance) on a 0–100 scale.
 *
 * Algorithms (see {@see extract()} `$params['algorithm']`):
 * - **lch** (default): LCh **L** — perceptual lightness from CIE L\*a\*b\* (library legacy default)
 * - **average**: (R + G + B) / 3 on gamma-encoded RGB
 * - **lightness**: (max(R,G,B) + min(R,G,B)) / 2 — HSV/HSL-style lightness midpoint
 * - **hsvValue**: HSV **V** = max(R,G,B) — Alvy Ray Smith, 1978
 * - **rec601**: ITU-R BT.601 luma on 8-bit RGB (0.299R + 0.587G + 0.114B)
 * - **rec709**: ITU-R BT.709 luma on 8-bit RGB (0.2126R + 0.7152G + 0.0722B)
 * - **cie1976Lab**: CIE 1976 L\*a\*b\* **L\***
 * - **ciecam02**: CIECAM02 lightness correlate **J** (average surround, D65)
 * - **ciecam16**: CIECAM16 lightness correlate **J** (average surround, D65)
 *
 * For linear-light relative luminance (XYZ Y) use {@see LuminanceExtractor} instead.
 */
final class BrightnessExtractor implements ExtractorInterface
{
    /** Default: LCh L (previous library behaviour). */
    public const string ALGORITHM_LCH = 'lch';

    /** Naïve RGB average. */
    public const string ALGORITHM_AVERAGE = 'average';

    /** (max + min) / 2 on RGB channels. */
    public const string ALGORITHM_LIGHTNESS = 'lightness';

    /** HSV value (max channel). */
    public const string ALGORITHM_HSV_VALUE = 'hsvValue';

    /** ITU-R BT.601 weighted sum on gamma-encoded RGB. */
    public const string ALGORITHM_REC601 = 'rec601';

    /** ITU-R BT.709 weighted sum on gamma-encoded RGB. */
    public const string ALGORITHM_REC709 = 'rec709';

    /** CIE 1976 L*a*b* L*. */
    public const string ALGORITHM_CIE1976_LAB = 'cie1976Lab';

    /** CIECAM02 J. */
    public const string ALGORITHM_CIECAM02 = 'ciecam02';

    /** CIECAM16 J. */
    public const string ALGORITHM_CIECAM16 = 'ciecam16';

    public function getName(): string
    {
        return 'brightness';
    }

    /**
     * @param mixed $params Optional associative array:
     *                      - `algorithm` (string): see class constants (default {@see ALGORITHM_LCH})
     *                      - `L_A` (float): adapting luminance cd/m² for CIECAM02/16 (optional)
     *                      - `Y_b` (float): background Y factor for CIECAM02/16 (optional)
     */
    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $algorithm = self::resolveAlgorithm($params);

        return match ($algorithm) {
            self::ALGORITHM_AVERAGE => self::brightnessFromAverage($color),
            self::ALGORITHM_LIGHTNESS => self::brightnessFromLightness($color),
            self::ALGORITHM_HSV_VALUE => self::brightnessFromHsvValue($color),
            self::ALGORITHM_REC601 => self::brightnessFromRec601($color),
            self::ALGORITHM_REC709 => self::brightnessFromRec709($color),
            self::ALGORITHM_CIE1976_LAB => self::brightnessFromLab($color),
            self::ALGORITHM_CIECAM02 => self::brightnessFromCieCam($color, $params, 'ciecam02'),
            self::ALGORITHM_CIECAM16 => self::brightnessFromCieCam($color, $params, 'ciecam16'),
            default => self::brightnessFromLch($color),
        };
    }

    /**
     * Human-readable label for an algorithm (for UI / API responses).
     */
    public static function getAlgorithmLabel(string $algorithm): string
    {
        return match (self::resolveAlgorithmKey($algorithm)) {
            self::ALGORITHM_AVERAGE => 'RGB average',
            self::ALGORITHM_LIGHTNESS => 'Lightness (max + min) / 2',
            self::ALGORITHM_HSV_VALUE => 'HSV value (max channel)',
            self::ALGORITHM_REC601 => 'Rec. 601 luma',
            self::ALGORITHM_REC709 => 'Rec. 709 luma',
            self::ALGORITHM_CIE1976_LAB => 'CIE 1976 L*a*b* L*',
            self::ALGORITHM_CIECAM02 => 'CIECAM02 J',
            self::ALGORITHM_CIECAM16 => 'CIECAM16 J',
            default => 'LCh L (perceptual lightness)',
        };
    }

    /**
     * @param mixed $params
     */
    public static function resolveAlgorithm(mixed $params): string
    {
        if (!is_array($params) || !array_key_exists('algorithm', $params)) {
            return self::ALGORITHM_LCH;
        }

        return self::resolveAlgorithmKey((string) $params['algorithm']);
    }

    private static function resolveAlgorithmKey(string $raw): string
    {
        $key = strtolower(trim($raw));

        return match ($key) {
            'lch', 'lstar', 'l*', 'perceptual', '' => self::ALGORITHM_LCH,
            'average', 'rgb', 'rgbaverage', 'grayscale', 'mean' => self::ALGORITHM_AVERAGE,
            'lightness', 'maxmin', 'max_min', 'midpoint', 'hsllightness' => self::ALGORITHM_LIGHTNESS,
            'hsvvalue', 'hsv', 'value', 'max', 'hsb', 'v' => self::ALGORITHM_HSV_VALUE,
            'rec601', 'bt601', 'itu601', '601' => self::ALGORITHM_REC601,
            'rec709', 'bt709', 'itu709', '709', 'srgbluma' => self::ALGORITHM_REC709,
            'cie1976lab', 'cie1976_lab', 'lab', 'lab1976', 'cielab' => self::ALGORITHM_CIE1976_LAB,
            'ciecam02', 'cam02', 'cam2002' => self::ALGORITHM_CIECAM02,
            'ciecam16', 'cam16' => self::ALGORITHM_CIECAM16,
            default => self::ALGORITHM_LCH,
        };
    }

    private static function brightnessFromLch(ColorInterface $color): float
    {
        return self::clampPercent($color->toLCh()->getChannel('l'));
    }

    private static function brightnessFromLab(ColorInterface $color): float
    {
        return self::clampPercent($color->toLab()->getChannel('l'));
    }

    private static function brightnessFromAverage(ColorInterface $color): float
    {
        [$r, $g, $b] = self::rgbChannels255($color);

        return self::clampPercent((($r + $g + $b) / 3.0 / 255.0) * 100.0);
    }

    private static function brightnessFromLightness(ColorInterface $color): float
    {
        [$r, $g, $b] = self::rgbChannels255($color);
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        return self::clampPercent((($max + $min) / 2.0 / 255.0) * 100.0);
    }

    private static function brightnessFromHsvValue(ColorInterface $color): float
    {
        return self::clampPercent($color->toHSV()->getChannel('v'));
    }

    private static function brightnessFromRec601(ColorInterface $color): float
    {
        [$r, $g, $b] = self::rgbChannels255($color);
        $y = 0.299 * $r + 0.587 * $g + 0.114 * $b;

        return self::clampPercent(($y / 255.0) * 100.0);
    }

    private static function brightnessFromRec709(ColorInterface $color): float
    {
        [$r, $g, $b] = self::rgbChannels255($color);
        $y = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        return self::clampPercent(($y / 255.0) * 100.0);
    }

    /**
     * @param mixed $params
     */
    private static function brightnessFromCieCam(ColorInterface $color, mixed $params, string $model): float
    {
        $xyz = $color->toXYZ();
        $white = CIEIlluminantData::getXYZ(CIEIlluminant::D65, CIEObserver::TwoDegree);

        $options = [];
        if (is_array($params)) {
            if (isset($params['L_A']) && is_numeric($params['L_A'])) {
                $options['L_A'] = (float) $params['L_A'];
            }
            if (isset($params['Y_b']) && is_numeric($params['Y_b'])) {
                $options['Y_b'] = (float) $params['Y_b'];
            }
        }

        return CieCamAppearance::lightnessJ(
            [
                'x' => $xyz->getChannel('x'),
                'y' => $xyz->getChannel('y'),
                'z' => $xyz->getChannel('z'),
            ],
            $white,
            $model,
            $options,
        );
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    private static function rgbChannels255(ColorInterface $color): array
    {
        $rgb = $color->toRGB();

        return [
            $rgb->getChannel('r'),
            $rgb->getChannel('g'),
            $rgb->getChannel('b'),
        ];
    }

    private static function clampPercent(float $value): float
    {
        return min(100.0, max(0.0, $value));
    }

    /**
     * Return a human-readable label for a brightness value (0–100).
     */
    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 50.0;
        if ($v < 20) {
            return 'very dark';
        }
        if ($v < 40) {
            return 'dark';
        }
        if ($v < 60) {
            return 'medium';
        }
        if ($v < 80) {
            return 'light';
        }

        return 'very light';
    }
}

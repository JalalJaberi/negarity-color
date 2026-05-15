<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Correlated color temperature (warm vs cold) from chromaticity vs the Planckian locus.
 *
 * Pipeline: sRGB (via library XYZ path = linear sRGB → XYZ) → CIE 1931 (x,y).
 *
 * Algorithms (see {@see extract()} `$params['algorithm']`):
 * - **McCamy** (default): cubic fit from (x,y) to CCT (Kelvin).
 * - **nearestPlanckianUcs1960**: (x,y) → CIE 1960 UCS (u,v), then brute-force nearest
 *   point on the Planckian locus (Kim / Lindbloom polynomials) in (u,v).
 */
final class TemperatureExtractor implements ExtractorInterface
{
    private const float KELVIN_COARSE_STEP = 40.0;

    private const float KELVIN_FINE_RADIUS = 120.0;

    private const float KELVIN_FINE_STEP = 1.0;

    /** Centre Kelvin for neutral on the [-1, 1] scale (~equal-energy / daylight). */
    private const float KELVIN_NEUTRAL = 6500.0;

    /** Approximately maps ~1500 K … ~11500 K into [-1, 1]. */
    private const float KELVIN_SCALE = 4200.0;

    /** McCamy (x,y) reference for CCT cubic (CIE 1931). */
    private const float MCCAMY_XE = 0.3320;

    private const float MCCAMY_YE = 0.1858;

    /** Default extractor algorithm key. */
    public const string ALGORITHM_MCCAMY = 'mccamy';

    /** Brute-force nearest Planckian locus search in CIE 1960 UCS. */
    public const string ALGORITHM_NEAREST_PLANCKIAN_UCS1960 = 'nearestPlanckianUcs1960';

    public function getName(): string
    {
        return 'temperature';
    }

    /**
     * @param mixed $params Optional associative array:
     *                      - `algorithm` (string): {@see ALGORITHM_MCCAMY} (default) or {@see ALGORITHM_NEAREST_PLANCKIAN_UCS1960}
     */
    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $xyz = $color->toXYZ();
        $X = $xyz->getChannel('x');
        $Y = $xyz->getChannel('y');
        $Z = $xyz->getChannel('z');

        $sum = $X + $Y + $Z;
        if ($sum <= 1e-9) {
            return 0.0;
        }

        $cx = $X / $sum;
        $cy = $Y / $sum;

        $algorithm = self::resolveAlgorithm($params);

        $kelvin = match ($algorithm) {
            self::ALGORITHM_NEAREST_PLANCKIAN_UCS1960 => self::kelvinNearestPlanckianLocusSearchUcs1960($cx, $cy),
            default => self::kelvinMcCamyApproximation($cx, $cy),
        };

        return self::kelvinToSignedUnit($kelvin);
    }

    /**
     * @param mixed $params
     */
    private static function resolveAlgorithm(mixed $params): string
    {
        if (!is_array($params)) {
            return self::ALGORITHM_MCCAMY;
        }

        if (!array_key_exists('algorithm', $params)) {
            return self::ALGORITHM_MCCAMY;
        }

        $key = strtolower(trim((string) $params['algorithm']));

        return match ($key) {
            'mccamy', 'mccamy1992', 'mccamy1993', '' => self::ALGORITHM_MCCAMY,
            'nearestplanckianucs1960',
            'nearest_planckian_ucs1960',
            'ucs1960',
            'brute',
            'bruteforce',
            'brute_force',
            'planckian_locus',
            'planckianlocus' => self::ALGORITHM_NEAREST_PLANCKIAN_UCS1960,
            default => self::ALGORITHM_MCCAMY,
        };
    }

    /**
     * McCamy cubic correlated color temperature from CIE 1931 chromaticity (x, y).
     *
     * @see https://en.wikipedia.org/wiki/Color_temperature#Approximation (McCamy)
     */
    private static function kelvinMcCamyApproximation(float $x, float $y): float
    {
        $denom = $y - self::MCCAMY_YE;
        if (abs($denom) < 1e-12) {
            return self::KELVIN_NEUTRAL;
        }

        $n = ($x - self::MCCAMY_XE) / $denom;

        $t = -437.0 * $n ** 3
            + 3601.0 * $n ** 2
            - 6861.0 * $n
            + 5514.31;

        return max(1000.0, min(250000.0, $t));
    }

    /**
     * Brute-force nearest Planckian locus search in CIE 1960 UCS: minimizes squared distance in (u, v).
     *
     * Pipeline: (x,y) → (u,v) → nearest Kelvin on locus → Kelvin.
     *
     * Planckian xy(T) follows Kim / Bruce Lindbloom-style polynomials on the black-body locus.
     *
     * @see https://www.brucelindbloom.com/Eqn_XYZ_to_xyb_T.html
     */
    private static function kelvinNearestPlanckianLocusSearchUcs1960(float $x, float $y): float
    {
        [$u, $v] = self::cie1931XyToUv1960($x, $y);

        return self::nearestPlanckianTemperatureKelvinUv($u, $v);
    }

    /**
     * CIE 1931 chromaticity (x,y) → CIE 1960 UCS (u,v).
     *
     * @return array{0: float, 1: float} u, v
     */
    private static function cie1931XyToUv1960(float $x, float $y): array
    {
        $denom = -2.0 * $x + 12.0 * $y + 3.0;
        if (abs($denom) < 1e-12) {
            return [0.0, 0.0];
        }

        return [
            4.0 * $x / $denom,
            6.0 * $y / $denom,
        ];
    }

    /**
     * Planckian locus CIE 1931 x,y for absolute temperature T (Kelvin).
     *
     * Polynomials from Kim / Bruce Lindbloom (black-body chromaticity approximation).
     */
    private static function planckianXyFromKelvin(float $t): array
    {
        $t = max(1000.0, min(250000.0, $t));

        if ($t <= 4000.0) {
            $x = (-0.2661239e9 / pow($t, 3.0))
                - (0.2343589e6 / pow($t, 2.0))
                + (0.8776956e3 / $t)
                + 0.179910;
        } else {
            $x = (-3.0258469e9 / pow($t, 3.0))
                + (2.1070379e6 / pow($t, 2.0))
                + (0.2226347e3 / $t)
                + 0.240390;
        }

        if ($t <= 2222.0) {
            $y = -1.1064814 * $x * $x * $x
                - 1.34811020 * $x * $x
                + 2.18555832 * $x
                - 0.20219683;
        } elseif ($t <= 4000.0) {
            $y = -0.9549476 * $x * $x * $x
                - 1.37418593 * $x * $x
                + 2.09137015 * $x
                - 0.16748867;
        } else {
            $y = 3.0817580 * $x * $x * $x
                - 5.7914224 * $x * $x
                + 3.75112997 * $x
                - 0.37001483;
        }

        return [$x, $y];
    }

    /**
     * Nearest Planckian temperature minimizing squared distance in CIE 1960 (u,v).
     */
    private static function nearestPlanckianTemperatureKelvinUv(float $u, float $v): float
    {
        $bestT = self::KELVIN_NEUTRAL;
        $bestD = PHP_FLOAT_MAX;

        for ($t = 1000.0; $t <= 25000.0; $t += self::KELVIN_COARSE_STEP) {
            [$pu, $pv] = self::planckianUv1960FromKelvin($t);
            $d = ($pu - $u) * ($pu - $u) + ($pv - $v) * ($pv - $v);
            if ($d < $bestD) {
                $bestD = $d;
                $bestT = $t;
            }
        }

        $t0 = max(1000.0, $bestT - self::KELVIN_FINE_RADIUS);
        $t1 = min(25000.0, $bestT + self::KELVIN_FINE_RADIUS);
        for ($t = $t0; $t <= $t1; $t += self::KELVIN_FINE_STEP) {
            [$pu, $pv] = self::planckianUv1960FromKelvin($t);
            $d = ($pu - $u) * ($pu - $u) + ($pv - $v) * ($pv - $v);
            if ($d < $bestD) {
                $bestD = $d;
                $bestT = $t;
            }
        }

        return $bestT;
    }

    /**
     * @return array{0: float, 1: float} u, v (1960 UCS)
     */
    private static function planckianUv1960FromKelvin(float $t): array
    {
        [$x, $y] = self::planckianXyFromKelvin($t);

        return self::cie1931XyToUv1960($x, $y);
    }

    /**
     * Map Kelvin to [-1, 1]: lower K (warmer light) → positive; higher K (cooler) → negative.
     */
    private static function kelvinToSignedUnit(float $kelvin): float
    {
        $kelvin = max(500.0, min(100000.0, $kelvin));
        $signed = (self::KELVIN_NEUTRAL - $kelvin) / self::KELVIN_SCALE;

        return max(-1.0, min(1.0, $signed));
    }

    /**
     * Return a human-readable label for a temperature value (-1 to 1).
     */
    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 0.0;
        if ($v < -0.6) {
            return 'cold';
        }
        if ($v < -0.2) {
            return 'cool';
        }
        if ($v <= 0.2) {
            return 'neutral';
        }
        if ($v <= 0.6) {
            return 'warm';
        }

        return 'hot';
    }
}

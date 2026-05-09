<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Correlated color temperature (warm vs cold) from chromaticity vs the Planckian locus.
 *
 * Pipeline: sRGB (via library XYZ path = linear sRGB → XYZ) → CIE 1931 (x,y) → CIE 1960 UCS (u,v)
 * → nearest point on the Planckian locus in (u,v) → Kelvin → normalized scalar in [-1, 1] for UI/sliders.
 *
 * Planckian xy(T) follows Kim / Bruce Lindbloom-style polynomials on the black-body locus.
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

    public function getName(): string
    {
        return 'temperature';
    }

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

        [$u, $v] = self::cie1931_xy_to_uv1960($cx, $cy);

        $t = self::nearest_planckian_temperature_kelvin_uv($u, $v);

        return self::kelvin_to_signed_unit($t);
    }

    /**
     * CIE 1931 chromaticity (x,y) → CIE 1960 UCS (u,v).
     *
     * @return array{0: float, 1: float} u, v
     */
    private static function cie1931_xy_to_uv1960(float $x, float $y): array
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
     *
     * @see https://www.brucelindbloom.com/Eqn_XYZ_to_xyb_T.html
     */
    private static function planckian_xy_from_kelvin(float $t): array
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
    private static function nearest_planckian_temperature_kelvin_uv(float $u, float $v): float
    {
        $bestT = self::KELVIN_NEUTRAL;
        $bestD = PHP_FLOAT_MAX;

        for ($t = 1000.0; $t <= 25000.0; $t += self::KELVIN_COARSE_STEP) {
            [$pu, $pv] = self::planckian_uv1960_from_kelvin($t);
            $d = ($pu - $u) * ($pu - $u) + ($pv - $v) * ($pv - $v);
            if ($d < $bestD) {
                $bestD = $d;
                $bestT = $t;
            }
        }

        $t0 = max(1000.0, $bestT - self::KELVIN_FINE_RADIUS);
        $t1 = min(25000.0, $bestT + self::KELVIN_FINE_RADIUS);
        for ($t = $t0; $t <= $t1; $t += self::KELVIN_FINE_STEP) {
            [$pu, $pv] = self::planckian_uv1960_from_kelvin($t);
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
    private static function planckian_uv1960_from_kelvin(float $t): array
    {
        [$x, $y] = self::planckian_xy_from_kelvin($t);

        return self::cie1931_xy_to_uv1960($x, $y);
    }

    /**
     * Map Kelvin to [-1, 1]: lower K (warmer light) → positive; higher K (cooler) → negative.
     */
    private static function kelvin_to_signed_unit(float $kelvin): float
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

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
 * - **McCamy** (default): cubic fit from (x,y) to CCT (Kelvin); see `$params['version']`.
 * - **nearestPlanckianUcs1960**: (x,y) → CIE 1960 UCS (u,v), then brute-force nearest
 *   point on the Planckian locus (Kim / Lindbloom polynomials) in (u,v).
 * - **krystek1985**: Krystek (1985) rational *u,v(T)* approximation; inverse CCT by
 *   iterative search minimizing distance in (u,v) on [1000, 15000] K.
 */
final class TemperatureExtractor implements ExtractorInterface
{
    private const float KELVIN_COARSE_STEP = 40.0;

    private const float KELVIN_FINE_RADIUS = 120.0;

    private const float KELVIN_FINE_STEP = 1.0;

    /** Krystek (1985) valid CCT domain (Kelvin). */
    private const float KRYSTEK_T_MIN = 1000.0;

    private const float KRYSTEK_T_MAX = 15000.0;

    private const float KRYSTEK_COARSE_STEP = 50.0;

    private const float KRYSTEK_FINE_RADIUS = 250.0;

    /** Centre Kelvin for neutral on the [-1, 1] scale (~equal-energy / daylight). */
    private const float KELVIN_NEUTRAL = 6500.0;

    /** Approximately maps ~1500 K … ~11500 K into [-1, 1]. */
    private const float KELVIN_SCALE = 4200.0;

    /** McCamy (x,y) reference for CCT cubic (CIE 1931). */
    private const float MCCAMY_XE = 0.3320;

    private const float MCCAMY_YE = 0.1858;

    /**
     * Max Euclidean distance in CIE 1960 (u,v) from the Planckian locus for McCamy to be trusted.
     * Beyond this, McCamy CCT is off-locus nonsense (e.g. saturated blue → ~1700 K).
     */
    private const float MCCAMY_MAX_PLANCKIAN_DISTANCE_UV = 0.01;

    /** Default extractor algorithm key. */
    public const string ALGORITHM_MCCAMY = 'mccamy';

    /** Brute-force nearest Planckian locus search in CIE 1960 UCS. */
    public const string ALGORITHM_NEAREST_PLANCKIAN_UCS1960 = 'nearestPlanckianUcs1960';

    /** Krystek (1985) Chebyshev-style rational approximation + iterative inverse. */
    public const string ALGORITHM_KRYSTEK1985 = 'krystek1985';

    /** McCamy (canonical 1992) cubic from (x,y) — default when `version` is omitted. */
    public const string VERSION_ORIGINAL = 'original';

    /** McCamy-style cubic with refined coefficients (library default before versioning). */
    public const string VERSION_REFINED = 'refined';

    /** Krystek forward mapping + iterative inverse (default for {@see ALGORITHM_KRYSTEK1985}). */
    public const string VERSION_CHEBYSHEV = 'chebyshev';

    public function getName(): string
    {
        return 'temperature';
    }

    /**
     * @param mixed $params Optional associative array:
     *                      - `algorithm` (string): {@see ALGORITHM_MCCAMY} (default), {@see ALGORITHM_NEAREST_PLANCKIAN_UCS1960}, or {@see ALGORITHM_KRYSTEK1985}
     *                      - `version` (string): McCamy — {@see VERSION_ORIGINAL} (default) or {@see VERSION_REFINED};
     *                        Krystek — {@see VERSION_CHEBYSHEV} (default, iterative inverse)
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
        $version = self::resolveVersion($params);

        $kelvin = match ($algorithm) {
            self::ALGORITHM_NEAREST_PLANCKIAN_UCS1960 => self::kelvinNearestPlanckianLocusSearchUcs1960($cx, $cy),
            self::ALGORITHM_KRYSTEK1985 => self::kelvinKrystek1985($cx, $cy),
            default => self::kelvinMcCamy($cx, $cy, $version),
        };

        return self::kelvinToSignedUnit($kelvin);
    }

    /**
     * Human-readable label for an algorithm version (for UI / API responses).
     */
    public static function getVersionLabel(string $algorithm, string $version): string
    {
        if ($algorithm === self::ALGORITHM_NEAREST_PLANCKIAN_UCS1960) {
            return 'Original';
        }

        if ($algorithm === self::ALGORITHM_KRYSTEK1985) {
            return match ($version) {
                self::VERSION_CHEBYSHEV, 'iterative', '1985' => 'Chebyshev + iterative (1985)',
                default => 'Chebyshev + iterative (1985)',
            };
        }

        return match ($version) {
            self::VERSION_REFINED => 'Refined',
            default => 'Original (1992)',
        };
    }

    /**
     * @param mixed $params
     */
    public static function resolveAlgorithm(mixed $params): string
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
            'krystek',
            'krystek1985',
            'krystek_1985' => self::ALGORITHM_KRYSTEK1985,
            default => self::ALGORITHM_MCCAMY,
        };
    }

    /**
     * Resolve McCamy (or other) algorithm variant; default is always {@see VERSION_ORIGINAL}.
     *
     * @param mixed $params
     */
    public static function resolveVersion(mixed $params): string
    {
        if (!is_array($params) || !array_key_exists('version', $params)) {
            return self::VERSION_ORIGINAL;
        }

        $key = strtolower(trim((string) $params['version']));

        return match ($key) {
            'original', 'mccamy1992', 'mccamy1993', 'canonical', 'canonical1992', '' => self::VERSION_ORIGINAL,
            'refined', 'updated', 'current' => self::VERSION_REFINED,
            'chebyshev', 'iterative', 'krystek', '1985' => self::VERSION_CHEBYSHEV,
            default => self::VERSION_ORIGINAL,
        };
    }

    /**
     * Krystek (1985): CCT from CIE 1960 (u,v) via iterative minimization of |uv(T) − uv_sample|.
     *
     * Forward uv(T) uses the rational approximations from Krystek (1985) / colour-science.
     *
     * @see https://doi.org/10.1002/col.5080100109
     */
    private static function kelvinKrystek1985(float $x, float $y): float
    {
        [$u, $v] = self::cie1931XyToUv1960($x, $y);

        return self::cctFromUvKrystek1985($u, $v);
    }

    /**
     * Krystek (1985) CIE 1960 UCS chromaticity from correlated colour temperature.
     *
     * @return array{0: float, 1: float} u, v
     */
    private static function cctToUvKrystek1985(float $t): array
    {
        $t = max(self::KRYSTEK_T_MIN, min(self::KRYSTEK_T_MAX, $t));
        $t2 = $t * $t;

        $u = (0.860117757 + 1.54118254e-4 * $t + 1.28641212e-7 * $t2)
            / (1.0 + 8.42420235e-4 * $t + 7.08145163e-7 * $t2);
        $v = (0.317398726 + 4.22806245e-5 * $t + 4.20481691e-8 * $t2)
            / (1.0 - 2.89741816e-5 * $t + 1.61456053e-7 * $t2);

        return [$u, $v];
    }

    /**
     * Euclidean distance in (u,v) between sample and Krystek approximation at T.
     */
    private static function krystekUvDistanceAtKelvin(float $t, float $u, float $v): float
    {
        [$pu, $pv] = self::cctToUvKrystek1985($t);
        $du = $pu - $u;
        $dv = $pv - $v;

        return sqrt($du * $du + $dv * $dv);
    }

    /**
     * Iterative inverse: find T in [1000, 15000] K minimizing krystek uv distance.
     */
    private static function cctFromUvKrystek1985(float $u, float $v): float
    {
        $bestT = self::KELVIN_NEUTRAL;
        $bestD = PHP_FLOAT_MAX;

        for ($t = self::KRYSTEK_T_MIN; $t <= self::KRYSTEK_T_MAX; $t += self::KRYSTEK_COARSE_STEP) {
            $d = self::krystekUvDistanceAtKelvin($t, $u, $v);
            if ($d < $bestD) {
                $bestD = $d;
                $bestT = $t;
            }
        }

        $tLo = max(self::KRYSTEK_T_MIN, $bestT - self::KRYSTEK_FINE_RADIUS);
        $tHi = min(self::KRYSTEK_T_MAX, $bestT + self::KRYSTEK_FINE_RADIUS);

        return self::goldenSectionMinKelvin(
            $tLo,
            $tHi,
            static fn(float $t): float => self::krystekUvDistanceAtKelvin($t, $u, $v),
            0.25
        );
    }

    /**
     * Golden-section search for minimum of f(T) on [lo, hi] (unimodal assumed).
     */
    private static function goldenSectionMinKelvin(float $lo, float $hi, callable $f, float $tol = 0.25): float
    {
        $phi = (sqrt(5.0) - 1.0) / 2.0;
        $a = $lo;
        $b = $hi;
        $c = $b - $phi * ($b - $a);
        $d = $a + $phi * ($b - $a);
        $fc = $f($c);
        $fd = $f($d);

        while (($b - $a) > $tol) {
            if ($fc < $fd) {
                $b = $d;
                $d = $c;
                $fd = $fc;
                $c = $b - $phi * ($b - $a);
                $fc = $f($c);
            } else {
                $a = $c;
                $c = $d;
                $fc = $fd;
                $d = $a + $phi * ($b - $a);
                $fd = $f($d);
            }
        }

        return ($a + $b) / 2.0;
    }

    /**
     * McCamy-family CCT (Kelvin) from CIE 1931 (x, y) for the given {@see resolveVersion()} key.
     *
     * When chromaticity is far from the Planckian locus, McCamy is unreliable; falls back to
     * {@see nearestPlanckianMatchUv()} (same search as {@see ALGORITHM_NEAREST_PLANCKIAN_UCS1960}).
     */
    private static function kelvinMcCamy(float $x, float $y, string $version): float
    {
        $kelvin = match ($version) {
            self::VERSION_REFINED => self::cctMcCamyRefined($x, $y),
            default => self::cctMcCamyOriginal($x, $y),
        };

        [$u, $v] = self::cie1931XyToUv1960($x, $y);
        $match = self::nearestPlanckianMatchUv($u, $v);
        $distanceUv = sqrt($match['distanceSq']);

        if ($distanceUv > self::MCCAMY_MAX_PLANCKIAN_DISTANCE_UV) {
            return $match['kelvin'];
        }

        return $kelvin;
    }

    /**
     * McCamy (canonical 1992): cubic correlated color temperature from CIE 1931 (x, y).
     *
     * @see https://en.wikipedia.org/wiki/Color_temperature#Approximation (McCamy)
     */
    private static function cctMcCamyOriginal(float $x, float $y): float
    {
        $denom = $y - self::MCCAMY_YE;
        if (abs($denom) < 1e-12) {
            return 6500.0;
        }

        $n = ($x - self::MCCAMY_XE) / $denom;

        $cct = -449.0 * $n ** 3
            + 3525.0 * $n ** 2
            - 6823.3 * $n
            + 5520.33;

        return max(1000.0, min(25000.0, $cct));
    }

    /**
     * McCamy-style cubic with refined coefficients (previous library default).
     */
    private static function cctMcCamyRefined(float $x, float $y): float
    {
        $denom = $y - self::MCCAMY_YE;
        if (abs($denom) < 1e-12) {
            return self::KELVIN_NEUTRAL;
        }

        $n = ($x - self::MCCAMY_XE) / $denom;

        $cct = -437.0 * $n ** 3
            + 3601.0 * $n ** 2
            - 6861.0 * $n
            + 5514.31;

        return max(1000.0, min(250000.0, $cct));
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

        return self::nearestPlanckianMatchUv($u, $v)['kelvin'];
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
     * Nearest point on the Planckian locus in CIE 1960 (u,v).
     *
     * @return array{kelvin: float, distanceSq: float}
     */
    private static function nearestPlanckianMatchUv(float $u, float $v): array
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

        return ['kelvin' => $bestT, 'distanceSq' => $bestD];
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

<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts chroma (neutral vs “colory”) on a 0–100 display scale.
 *
 * Algorithms (see {@see extract()} `$params['algorithm']`):
 * - **oklch** (default): OKLab/OKLCH chroma C = √(a² + b²), normalized with divisor **0.4**
 * - **cie1976Lab**: CIE 1976 L*a*b* chroma C* = √(a² + b²), normalized with divisor **150**
 * - **cie1976Luv**: CIE 1976 L*u*v* chroma C*uv = √(u*² + v*²), normalized with divisor **150**
 */
final class ChromaExtractor implements ExtractorInterface
{
    /** OKLCH / OKLab chroma divisor for 0–100 mapping (typical display-gamut peak ≈ 0.4). */
    private const float OKLCH_C_MAX = 0.4;

    /** Practical sRGB upper bound for C* in Lab and Luv when mapping to 0–100. */
    private const float CIE76_C_STAR_MAX = 150.0;

    /** Default: OKLab-based chroma (OKLCH C). */
    public const string ALGORITHM_OKLCH = 'oklch';

    /** CIE 1976 L*a*b* C* (previous library default). */
    public const string ALGORITHM_CIE1976_LAB = 'cie1976Lab';

    /** CIE 1976 L*u*v* C*uv. */
    public const string ALGORITHM_CIE1976_LUV = 'cie1976Luv';

    public function getName(): string
    {
        return 'chroma';
    }

    /**
     * @param mixed $params Optional associative array:
     *                      - `algorithm` (string): {@see ALGORITHM_OKLCH} (default),
     *                        {@see ALGORITHM_CIE1976_LAB}, or {@see ALGORITHM_CIE1976_LUV}
     */
    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $algorithm = self::resolveAlgorithm($params);

        return match ($algorithm) {
            self::ALGORITHM_CIE1976_LAB => self::chromaFromCie1976Lab($color),
            self::ALGORITHM_CIE1976_LUV => self::chromaFromCie1976Luv($color),
            default => self::chromaFromOklch($color),
        };
    }

    /**
     * Human-readable label for an algorithm (for UI / API responses).
     */
    public static function getAlgorithmLabel(string $algorithm): string
    {
        return match (self::resolveAlgorithmKey($algorithm)) {
            self::ALGORITHM_CIE1976_LAB => 'CIE 1976 L*a*b*',
            self::ALGORITHM_CIE1976_LUV => 'CIE 1976 L*u*v*',
            default => 'OKLCH',
        };
    }

    /**
     * @param mixed $params
     */
    public static function resolveAlgorithm(mixed $params): string
    {
        if (!is_array($params) || !array_key_exists('algorithm', $params)) {
            return self::ALGORITHM_OKLCH;
        }

        return self::resolveAlgorithmKey((string) $params['algorithm']);
    }

    private static function resolveAlgorithmKey(string $raw): string
    {
        $key = strtolower(trim($raw));

        return match ($key) {
            'oklch', 'oklab', 'ok', '' => self::ALGORITHM_OKLCH,
            'cie1976lab', 'cie1976_lab', 'lab', 'lab1976', 'cielab', 'cstar', 'c*' => self::ALGORITHM_CIE1976_LAB,
            'cie1976luv', 'cie1976_luv', 'luv', 'luv1976', 'cieluv', 'cieluv1976', 'c*uv' => self::ALGORITHM_CIE1976_LUV,
            default => self::ALGORITHM_OKLCH,
        };
    }

    /**
     * OKLCH chroma: C = √(a² + b²) from OKLab (equivalent to {@see toOklch()} `c`).
     */
    private static function chromaFromOklch(ColorInterface $color): float
    {
        $oklab = $color->toOklab();
        $a = $oklab->getChannel('a');
        $b = $oklab->getChannel('b');
        $chroma = sqrt($a * $a + $b * $b);

        return self::normalizeToPercent($chroma, self::OKLCH_C_MAX);
    }

    private static function chromaFromCie1976Lab(ColorInterface $color): float
    {
        $lab = $color->toLab();
        $a = $lab->getChannel('a');
        $b = $lab->getChannel('b');
        $cStar = sqrt($a * $a + $b * $b);

        return self::normalizeToPercent($cStar, self::CIE76_C_STAR_MAX);
    }

    private static function chromaFromCie1976Luv(ColorInterface $color): float
    {
        $luv = $color->toLuv();
        $u = $luv->getChannel('u');
        $v = $luv->getChannel('v');
        $cStar = sqrt($u * $u + $v * $v);

        return self::normalizeToPercent($cStar, self::CIE76_C_STAR_MAX);
    }

    private static function normalizeToPercent(float $chroma, float $divisor): float
    {
        if ($divisor <= 0.0) {
            return 0.0;
        }

        $normalized = min(100.0, ($chroma / $divisor) * 100.0);

        return max(0.0, $normalized);
    }

    /**
     * Return a human-readable label for a chroma value (0–100).
     */
    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 50.0;
        if ($v < 5) {
            return 'neutral';
        }
        if ($v < 25) {
            return 'low';
        }
        if ($v < 55) {
            return 'medium';
        }
        if ($v < 85) {
            return 'high';
        }

        return 'colory';
    }
}

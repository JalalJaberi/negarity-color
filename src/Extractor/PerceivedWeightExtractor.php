<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts perceived weight (visual heaviness) on a 0–100 display scale.
 *
 * Darker and more saturated colors tend to feel heavier. Algorithms (see {@see extract()} `$params['algorithm']`):
 * - **brightnessChromaLinear** (default): 0.7·(100−L*) + 0.3·chromaNorm·100 — weighted sum of darkness and chroma
 * - **brightnessChromaMultiplication**: (100−L*) × (1 + k·C* / 130) — multiplicative chroma boost on darkness
 *
 * Both use CIE LCh **L** as L* and **c** as C* via {@see toLCh()}.
 */
final class PerceivedWeightExtractor implements ExtractorInterface
{
    /** Practical sRGB upper bound for LCh C when normalizing chroma. */
    private const float LCH_C_MAX_APPROX = 130.0;

    /** Default darkness weight in the linear model. */
    private const float LINEAR_DARKNESS_WEIGHT = 0.7;

    /** Default chroma weight in the linear model. */
    private const float LINEAR_CHROMA_WEIGHT = 0.3;

    /** Default k in the multiplication model (applied to C* / 130). */
    private const float DEFAULT_MULTIPLICATION_K = 0.5;

    /** Default: linear brightness–chroma estimation (library legacy). */
    public const string ALGORITHM_BRIGHTNESS_CHROMA_LINEAR = 'brightnessChromaLinear';

    /** Multiplicative: (100−L*) × (1 + k·C* / 130). */
    public const string ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION = 'brightnessChromaMultiplication';

    public function getName(): string
    {
        return 'perceived_weight';
    }

    /**
     * @param mixed $params Optional associative array:
     *                      - `algorithm` (string): {@see ALGORITHM_BRIGHTNESS_CHROMA_LINEAR} (default)
     *                        or {@see ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION}
     *                      - `k` (float): chroma factor for multiplication (default 0.5)
     */
    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $algorithm = self::resolveAlgorithm($params);

        return match ($algorithm) {
            self::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION => self::weightFromMultiplication($color, $params),
            default => self::weightFromLinear($color),
        };
    }

    /**
     * Human-readable label for an algorithm (for UI / API responses).
     */
    public static function getAlgorithmLabel(string $algorithm): string
    {
        return match (self::resolveAlgorithmKey($algorithm)) {
            self::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION => 'Brightness × chroma (multiplication)',
            default => 'Brightness + chroma (linear)',
        };
    }

    /**
     * @param mixed $params
     */
    public static function resolveAlgorithm(mixed $params): string
    {
        if (!is_array($params) || !array_key_exists('algorithm', $params)) {
            return self::ALGORITHM_BRIGHTNESS_CHROMA_LINEAR;
        }

        return self::resolveAlgorithmKey((string) $params['algorithm']);
    }

    private static function resolveAlgorithmKey(string $raw): string
    {
        $key = strtolower(trim($raw));

        return match ($key) {
            'brightnesschromamultiplication', 'brightness_chroma_multiplication', 'multiplication',
            'multiplicative', 'multiply', 'product' => self::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION,
            'brightnesschromalinear', 'brightness_chroma_linear', 'linear', 'sum', 'additive', '' => self::ALGORITHM_BRIGHTNESS_CHROMA_LINEAR,
            default => self::ALGORITHM_BRIGHTNESS_CHROMA_LINEAR,
        };
    }

    /**
     * 0.7·(100−L*) + 0.3·(C* / 130)·100
     */
    private static function weightFromLinear(ColorInterface $color): float
    {
        [$darkness, $chromaNorm] = self::lchDarknessAndChromaNorm($color);
        $weight = $darkness * self::LINEAR_DARKNESS_WEIGHT + $chromaNorm * 100.0 * self::LINEAR_CHROMA_WEIGHT;

        return self::clampPercent($weight);
    }

    /**
     * (100−L*) × (1 + k·C* / 130)
     *
     * @param mixed $params
     */
    private static function weightFromMultiplication(ColorInterface $color, mixed $params): float
    {
        [$darkness, $chromaNorm] = self::lchDarknessAndChromaNorm($color);
        $k = self::resolveMultiplicationK($params);
        $weight = $darkness * (1.0 + $k * $chromaNorm);

        return self::clampPercent($weight);
    }

    /**
     * @return array{0: float, 1: float} [darkness (100−L*), chromaNorm (0–1)]
     */
    private static function lchDarknessAndChromaNorm(ColorInterface $color): array
    {
        $lch = $color->toLCh();
        $l = $lch->getChannel('l');
        $c = $lch->getChannel('c');
        $darkness = 100.0 - $l;
        $chromaNorm = min(1.0, max(0.0, $c / self::LCH_C_MAX_APPROX));

        return [$darkness, $chromaNorm];
    }

    /**
     * @param mixed $params
     */
    private static function resolveMultiplicationK(mixed $params): float
    {
        if (!is_array($params) || !array_key_exists('k', $params) || !is_numeric($params['k'])) {
            return self::DEFAULT_MULTIPLICATION_K;
        }

        return max(0.0, (float) $params['k']);
    }

    private static function clampPercent(float $value): float
    {
        return min(100.0, max(0.0, $value));
    }

    /**
     * Return a human-readable label for a perceived weight value (0–100).
     */
    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 50.0;
        if ($v < 25) {
            return 'light';
        }
        if ($v < 60) {
            return 'medium';
        }

        return 'heavy';
    }
}

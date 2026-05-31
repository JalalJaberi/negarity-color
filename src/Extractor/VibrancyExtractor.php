<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts vibrancy (dull vs vibrant) on a 0–100 display scale.
 *
 * Vibrancy peaks at mid lightness with high chroma. Algorithms (see {@see extract()} `$params['algorithm']`):
 * - **midtoneChromaIndex** (default): C_norm × midPeak × 100 — triangular lightness envelope
 * - **gaussianVibrancyIndex**: C_norm × exp(−(L*−μ)² / (2σ²)) × 100 — Gaussian lightness envelope
 *
 * Both use CIE LCh **L** as L* and **c** as C* via {@see toLCh()}.
 */
final class VibrancyExtractor implements ExtractorInterface
{
    /** Practical sRGB upper bound for LCh C when normalizing chroma. */
    private const float LCH_C_MAX_APPROX = 130.0;

    /** Default Gaussian centre μ (L* on 0–100). */
    private const float DEFAULT_GAUSSIAN_MU = 50.0;

    /** Default Gaussian width σ (L* units). */
    private const float DEFAULT_GAUSSIAN_SIGMA = 25.0;

    /** Default: triangular midtone chroma index (library legacy). */
    public const string ALGORITHM_MIDTONE_CHROMA_INDEX = 'midtoneChromaIndex';

    /** Gaussian vibrancy: C_norm × exp(−(L*−μ)² / (2σ²)). */
    public const string ALGORITHM_GAUSSIAN_VIBRANCY_INDEX = 'gaussianVibrancyIndex';

    public function getName(): string
    {
        return 'vibrancy';
    }

    /**
     * @param mixed $params Optional associative array:
     *                      - `algorithm` (string): {@see ALGORITHM_MIDTONE_CHROMA_INDEX} (default)
     *                        or {@see ALGORITHM_GAUSSIAN_VIBRANCY_INDEX}
     *                      - `mu` (float): Gaussian centre μ (default 50)
     *                      - `sigma` (float): Gaussian width σ (default 25)
     */
    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $algorithm = self::resolveAlgorithm($params);

        return match ($algorithm) {
            self::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX => self::vibrancyFromGaussian($color, $params),
            default => self::vibrancyFromMidtoneChroma($color),
        };
    }

    /**
     * Human-readable label for an algorithm (for UI / API responses).
     */
    public static function getAlgorithmLabel(string $algorithm): string
    {
        return match (self::resolveAlgorithmKey($algorithm)) {
            self::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX => 'Gaussian vibrancy index',
            default => 'Midtone chroma index',
        };
    }

    /**
     * @param mixed $params
     */
    public static function resolveAlgorithm(mixed $params): string
    {
        if (!is_array($params) || !array_key_exists('algorithm', $params)) {
            return self::ALGORITHM_MIDTONE_CHROMA_INDEX;
        }

        return self::resolveAlgorithmKey((string) $params['algorithm']);
    }

    private static function resolveAlgorithmKey(string $raw): string
    {
        $key = strtolower(trim($raw));

        return match ($key) {
            'gaussianvibrancyindex', 'gaussian_vibrancy_index', 'gaussian', 'gauss', 'normal', 'bell' => self::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX,
            'midtonechromaindex', 'midtone_chroma_index', 'midtone', 'triangle', 'triangular', 'chroma_index', 'mid_peak', '' => self::ALGORITHM_MIDTONE_CHROMA_INDEX,
            default => self::ALGORITHM_MIDTONE_CHROMA_INDEX,
        };
    }

    /**
     * C_norm × (1 − 2|L* / 100 − 0.5|) × 100
     */
    private static function vibrancyFromMidtoneChroma(ColorInterface $color): float
    {
        [$l, $chromaNorm] = self::lchLightnessAndChromaNorm($color);
        $midPeak = 1.0 - 2.0 * abs($l / 100.0 - 0.5);
        $vibrancy = $chromaNorm * max(0.0, $midPeak) * 100.0;

        return self::clampPercent($vibrancy);
    }

    /**
     * C_norm × exp(−(L*−μ)² / (2σ²)) × 100
     *
     * @param mixed $params
     */
    private static function vibrancyFromGaussian(ColorInterface $color, mixed $params): float
    {
        [$l, $chromaNorm] = self::lchLightnessAndChromaNorm($color);
        $mu = self::resolveGaussianMu($params);
        $sigma = self::resolveGaussianSigma($params);
        $sigmaSq = max(1e-6, $sigma * $sigma);
        $envelope = exp(-(($l - $mu) ** 2) / (2.0 * $sigmaSq));
        $vibrancy = $chromaNorm * $envelope * 100.0;

        return self::clampPercent($vibrancy);
    }

    /**
     * @return array{0: float, 1: float} [L*, chromaNorm (0–1)]
     */
    private static function lchLightnessAndChromaNorm(ColorInterface $color): array
    {
        $lch = $color->toLCh();
        $l = $lch->getChannel('l');
        $c = $lch->getChannel('c');
        $chromaNorm = min(1.0, max(0.0, $c / self::LCH_C_MAX_APPROX));

        return [$l, $chromaNorm];
    }

    /**
     * @param mixed $params
     */
    private static function resolveGaussianMu(mixed $params): float
    {
        if (!is_array($params) || !array_key_exists('mu', $params) || !is_numeric($params['mu'])) {
            return self::DEFAULT_GAUSSIAN_MU;
        }

        return (float) $params['mu'];
    }

    /**
     * @param mixed $params
     */
    private static function resolveGaussianSigma(mixed $params): float
    {
        if (!is_array($params) || !array_key_exists('sigma', $params) || !is_numeric($params['sigma'])) {
            return self::DEFAULT_GAUSSIAN_SIGMA;
        }

        return max(1e-3, (float) $params['sigma']);
    }

    private static function clampPercent(float $value): float
    {
        return min(100.0, max(0.0, $value));
    }

    /**
     * Return a human-readable label for a vibrancy value (0–100).
     */
    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 50.0;
        if ($v < 15) {
            return 'low';
        }
        if ($v < 40) {
            return 'moderate';
        }
        if ($v < 70) {
            return 'high';
        }

        return 'vibrant';
    }
}

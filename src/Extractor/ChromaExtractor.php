<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts chroma (neutral vs colory).
 * Value: 0 (neutral) to 100 (high chroma), from Lab C* = sqrt(a² + b²), normalized.
 */
final class ChromaExtractor implements ExtractorInterface
{
    /** Approximate upper bound for C* when mapping to 0–100 display scale. */
    private const LAB_C_STAR_MAX_APPROX = 130.0;

    public function getName(): string
    {
        return 'chroma';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $lab = $color->toLab();
        $a = $lab->getChannel('a');
        $b = $lab->getChannel('b');

        $cStar = sqrt($a * $a + $b * $b);

        // practical sRGB normalization
        $normalized = ($cStar / 150.0) * 100.0;

        return min(100.0, max(0.0, $normalized));
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

<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts chroma (neutral vs colory).
 * Value: 0 (neutral) to 100 (high chroma), from LCh C normalized.
 */
final class ChromaExtractor implements ExtractorInterface
{
    private const LCH_C_MAX_APPROX = 130.0;

    public function getName(): string
    {
        return 'chroma';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $lch = $color->toLCh();
        $c = $lch->getChannel('c');
        return min(100.0, max(0.0, ($c / self::LCH_C_MAX_APPROX) * 100));
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

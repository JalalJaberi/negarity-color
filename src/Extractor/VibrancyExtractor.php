<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts vibrancy (dull vs vibrant).
 * Vibrancy is highest at mid lightness with high chroma. Value: 0 to 100.
 */
final class VibrancyExtractor implements ExtractorInterface
{
    private const LCH_C_MAX_APPROX = 130.0;

    public function getName(): string
    {
        return 'vibrancy';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $lch = $color->toLCh();
        $l = $lch->getChannel('l');
        $c = $lch->getChannel('c');
        $chromaNorm = min(1.0, $c / self::LCH_C_MAX_APPROX);
        $midPeak = 1.0 - 2.0 * abs($l / 100.0 - 0.5);
        $vibrancy = $chromaNorm * max(0, $midPeak) * 100;
        return min(100.0, max(0.0, $vibrancy));
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

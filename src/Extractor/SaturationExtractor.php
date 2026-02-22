<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts saturation / intensity (vivid vs dull).
 * Value: 0 (achromatic) to 100 (fully saturated), from LCh chroma normalized by max chroma at that L, or HSL S.
 * Uses LCh C for perceptual consistency; normalized to 0–100 scale using a typical max (~130 for sRGB).
 */
final class SaturationExtractor implements ExtractorInterface
{
    private const LCH_C_MAX_APPROX = 130.0;

    public function getName(): string
    {
        return 'saturation';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $lch = $color->toLCh();
        $c = $lch->getChannel('c');
        return min(100.0, max(0.0, ($c / self::LCH_C_MAX_APPROX) * 100));
    }

    /**
     * Return a human-readable label for a saturation value (0–100).
     */
    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 50.0;
        if ($v < 10) {
            return 'dull';
        }
        if ($v < 25) {
            return 'muted';
        }
        if ($v < 45) {
            return 'washed';
        }
        if ($v < 70) {
            return 'moderate';
        }
        return 'vivid';
    }
}

<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts perceived weight (light vs heavy).
 * Darker and more saturated colors feel heavier. Value: 0 (light) to 100 (heavy).
 */
final class PerceivedWeightExtractor implements ExtractorInterface
{
    private const LCH_C_MAX_APPROX = 130.0;

    public function getName(): string
    {
        return 'perceived_weight';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $lch = $color->toLCh();
        $l = $lch->getChannel('l');
        $c = $lch->getChannel('c');
        $darkness = 100.0 - $l;
        $chromaNorm = min(1.0, $c / self::LCH_C_MAX_APPROX);
        $weight = $darkness * 0.7 + $chromaNorm * 100 * 0.3;
        return min(100.0, max(0.0, $weight));
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

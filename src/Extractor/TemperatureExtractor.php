<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts color temperature from hue (warm vs cold).
 * Value: -1 (cold) to 1 (warm), based on HSL hue.
 */
final class TemperatureExtractor implements ExtractorInterface
{
    public function getName(): string
    {
        return 'temperature';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $hsl = $color->toHSL();
        $h = $hsl->getChannel('h');
        return (float) cos(deg2rad($h - 60));
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

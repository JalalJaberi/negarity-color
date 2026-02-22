<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts perceived brightness (lightness).
 * Value: 0 (dark) to 100 (light), from LCh L channel.
 */
final class BrightnessExtractor implements ExtractorInterface
{
    public function getName(): string
    {
        return 'brightness';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $lch = $color->toLCh();
        return $lch->getChannel('l');
    }

    /**
     * Return a human-readable label for a brightness value (0–100).
     */
    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 50.0;
        if ($v < 20) {
            return 'very dark';
        }
        if ($v < 40) {
            return 'dark';
        }
        if ($v < 60) {
            return 'medium';
        }
        if ($v < 80) {
            return 'light';
        }
        return 'very light';
    }
}

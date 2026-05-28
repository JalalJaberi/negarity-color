<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts relative luminance (CIE XYZ Y) on a 0–100 display scale.
 *
 * Uses the library’s {@see toXYZ()} path: linear sRGB (or source space → XYZ) with the
 * standard D65 matrix row Y = 0.2126729 R + 0.7151522 G + 0.0721750 B (IEC 61966-2-1 / Rec. 709).
 * XYZ Y is stored on a 0–100 percentage scale, matching {@see BrightnessExtractor} slider range
 * but measuring physical luminance, not perceptual LCh lightness.
 */
final class LuminanceExtractor implements ExtractorInterface
{
    public function getName(): string
    {
        return 'luminance';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $xyz = $color->toXYZ();
        $y = $xyz->getChannel('y');

        return min(100.0, max(0.0, $y));
    }

    /**
     * Return a human-readable label for a luminance value (0–100).
     */
    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 50.0;
        if ($v < 10) {
            return 'very dark';
        }
        if ($v < 25) {
            return 'dark';
        }
        if ($v < 50) {
            return 'medium';
        }
        if ($v < 75) {
            return 'light';
        }

        return 'very light';
    }
}

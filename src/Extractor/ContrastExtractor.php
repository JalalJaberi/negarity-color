<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\RGB;

/**
 * Extracts contrast ratio (WCAG) between the color and another color.
 * Params: ColorInterface (other color), or 'white', or 'black'.
 * Value: contrast ratio (1–21).
 */
final class ContrastExtractor implements ExtractorInterface
{
    public function getName(): string
    {
        return 'contrast';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $other = $this->resolveOther($params);
        $l1 = self::relativeLuminance($color);
        $l2 = self::relativeLuminance($other);
        $high = max($l1, $l2);
        $low = min($l1, $l2);
        return (float) (($high + 0.05) / ($low + 0.05));
    }

    /**
     * Return a human-readable label for a contrast ratio (e.g. WCAG level).
     */
    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 1.0;
        if ($v < 3) {
            return 'fails';
        }
        if ($v < 4.5) {
            return 'AA large';
        }
        if ($v < 7) {
            return 'AA';
        }
        return 'AAA';
    }

    private function resolveOther(mixed $params): ColorInterface
    {
        if ($params === 'white' || $params === null) {
            return new Color(RGB::class, ['r' => 255, 'g' => 255, 'b' => 255]);
        }
        if ($params === 'black') {
            return new Color(RGB::class, ['r' => 0, 'g' => 0, 'b' => 0]);
        }
        if ($params instanceof ColorInterface) {
            return $params;
        }
        return new Color(RGB::class, ['r' => 255, 'g' => 255, 'b' => 255]);
    }

    private static function relativeLuminance(ColorInterface $color): float
    {
        $rgb = $color->toRGB();
        $r = $rgb->getChannel('r') / 255;
        $g = $rgb->getChannel('g') / 255;
        $b = $rgb->getChannel('b') / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
        $g = $g <= 0.03928 ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
        $b = $b <= 0.03928 ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}

<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Extracts saturation / intensity (vivid vs dull) on a 0–100 display scale.
 *
 * Algorithms (see {@see extract()} `$params['algorithm']`):
 * - **hsv** (default): HSV *S* — Alvy Ray Smith, SIGGRAPH 1978; *S* = (V − min) / V
 * - **hsl**: HSL *S* — Foley & van Dam, *Computer Graphics*; lightness-relative *S*
 *
 * For perceptual absolute colorfulness use {@see ChromaExtractor} instead.
 */
final class SaturationExtractor implements ExtractorInterface
{
    /** Default: HSV saturation (Alvy Ray Smith, 1978). */
    public const string ALGORITHM_HSV = 'hsv';

    /** HSL saturation (Foley & van Dam). */
    public const string ALGORITHM_HSL = 'hsl';

    public function getName(): string
    {
        return 'saturation';
    }

    /**
     * @param mixed $params Optional associative array:
     *                      - `algorithm` (string): {@see ALGORITHM_HSV} (default) or {@see ALGORITHM_HSL}
     */
    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $algorithm = self::resolveAlgorithm($params);

        return match ($algorithm) {
            self::ALGORITHM_HSL => self::saturationFromHsl($color),
            default => self::saturationFromHsv($color),
        };
    }

    /**
     * Human-readable label for an algorithm (for UI / API responses).
     */
    public static function getAlgorithmLabel(string $algorithm): string
    {
        return match (self::resolveAlgorithmKey($algorithm)) {
            self::ALGORITHM_HSL => 'HSL (Foley & van Dam)',
            default => 'HSV (Alvy Ray Smith, 1978)',
        };
    }

    /**
     * @param mixed $params
     */
    public static function resolveAlgorithm(mixed $params): string
    {
        if (!is_array($params) || !array_key_exists('algorithm', $params)) {
            return self::ALGORITHM_HSV;
        }

        return self::resolveAlgorithmKey((string) $params['algorithm']);
    }

    private static function resolveAlgorithmKey(string $raw): string
    {
        $key = strtolower(trim($raw));

        return match ($key) {
            'hsl', 'foley', 'vandam', 'foley_vandam', 'foleyvandam' => self::ALGORITHM_HSL,
            'hsv', 'alvy', 'smith', 'alvyray', 'siggraph1978', 'siggraph', '' => self::ALGORITHM_HSV,
            default => self::ALGORITHM_HSV,
        };
    }

    /**
     * HSV S from {@see toHSV()} (0–100).
     */
    private static function saturationFromHsv(ColorInterface $color): float
    {
        return self::clampPercent($color->toHSV()->getChannel('s'));
    }

    /**
     * HSL S from {@see toHSL()} (0–100).
     */
    private static function saturationFromHsl(ColorInterface $color): float
    {
        return self::clampPercent($color->toHSL()->getChannel('s'));
    }

    private static function clampPercent(float $value): float
    {
        return min(100.0, max(0.0, $value));
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

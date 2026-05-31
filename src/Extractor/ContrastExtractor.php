<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\RGB;

/**
 * Extracts contrast between a foreground color and a reference (background).
 *
 * Algorithms (see {@see extract()} `$params`):
 * - **wcagContrastRatio** (default): WCAG 2.x relative luminance ratio (1–21)
 * - **michelsonContrast**: (L_max − L_min) / (L_max + L_min) on 0–100 scale
 * - **weberContrast**: (L_target − L_background) / L_background on percent scale
 * - **rmsContrast**: |L₁ − L₂| / √(½(L₁² + L₂²)) on 0–100 scale
 * - **deltaE76**: CIE76 ΔE*ab in Lab (0–100+)
 *
 * `$params` may be `null`, `'white'`, `'black'`, a {@see ColorInterface}, or an array with
 * `algorithm`, `contrastWith` / `reference`.
 */
final class ContrastExtractor implements ExtractorInterface
{
    private const float WEBER_L_MIN = 0.05;

    /** Default: WCAG 2.x contrast ratio. */
    public const string ALGORITHM_WCAG_CONTRAST_RATIO = 'wcagContrastRatio';

    /** Michelson: (L_max − L_min) / (L_max + L_min). */
    public const string ALGORITHM_MICHELSON_CONTRAST = 'michelsonContrast';

    /** Weber: (L_target − L_background) / L_background. */
    public const string ALGORITHM_WEBER_CONTRAST = 'weberContrast';

    /** RMS: |L₁ − L₂| / √(½(L₁² + L₂²)). */
    public const string ALGORITHM_RMS_CONTRAST = 'rmsContrast';

    /** CIE 1976 ΔE*ab. */
    public const string ALGORITHM_DELTA_E76 = 'deltaE76';

    public function getName(): string
    {
        return 'contrast';
    }

    /**
     * @param mixed $params Reference color or options array (see class docblock).
     */
    public function extract(ColorInterface $color, mixed $params = null): float
    {
        [$algorithm, $reference] = self::resolveParams($params);
        $other = self::resolveReferenceColor($reference);

        return match ($algorithm) {
            self::ALGORITHM_MICHELSON_CONTRAST => self::michelsonContrast($color, $other),
            self::ALGORITHM_WEBER_CONTRAST => self::weberContrast($color, $other),
            self::ALGORITHM_RMS_CONTRAST => self::rmsContrast($color, $other),
            self::ALGORITHM_DELTA_E76 => self::deltaE76($color, $other),
            default => self::wcagContrastRatio($color, $other),
        };
    }

    /**
     * Human-readable label for an algorithm (for UI / API responses).
     */
    public static function getAlgorithmLabel(string $algorithm): string
    {
        return match (self::resolveAlgorithmKey($algorithm)) {
            self::ALGORITHM_MICHELSON_CONTRAST => 'Michelson contrast',
            self::ALGORITHM_WEBER_CONTRAST => 'Weber contrast',
            self::ALGORITHM_RMS_CONTRAST => 'RMS contrast',
            self::ALGORITHM_DELTA_E76 => 'ΔE76 (CIE 1976)',
            default => 'WCAG contrast ratio',
        };
    }

    /**
     * Slider / display range [min, max] for an algorithm.
     *
     * @return array{0: float, 1: float}
     */
    public static function getDisplayRange(string $algorithm): array
    {
        return match (self::resolveAlgorithmKey($algorithm)) {
            self::ALGORITHM_WCAG_CONTRAST_RATIO => [1.0, 21.0],
            self::ALGORITHM_WEBER_CONTRAST => [-100.0, 400.0],
            self::ALGORITHM_DELTA_E76 => [0.0, 100.0],
            default => [0.0, 100.0],
        };
    }

    /**
     * @param mixed $params
     * @return array{0: string, 1: string|ColorInterface}
     */
    public static function resolveParams(mixed $params): array
    {
        if ($params instanceof ColorInterface) {
            return [self::ALGORITHM_WCAG_CONTRAST_RATIO, $params];
        }

        if ($params === null || $params === 'white') {
            return [self::ALGORITHM_WCAG_CONTRAST_RATIO, 'white'];
        }

        if ($params === 'black') {
            return [self::ALGORITHM_WCAG_CONTRAST_RATIO, 'black'];
        }

        if (!is_array($params)) {
            return [self::ALGORITHM_WCAG_CONTRAST_RATIO, 'white'];
        }

        $algorithm = self::resolveAlgorithm($params);
        $reference = 'white';
        if (array_key_exists('contrastWith', $params)) {
            $reference = $params['contrastWith'];
        } elseif (array_key_exists('reference', $params)) {
            $reference = $params['reference'];
        }

        return [$algorithm, $reference];
    }

    /**
     * @param mixed $params
     */
    public static function resolveAlgorithm(mixed $params): string
    {
        if (!is_array($params) || !array_key_exists('algorithm', $params)) {
            return self::ALGORITHM_WCAG_CONTRAST_RATIO;
        }

        return self::resolveAlgorithmKey((string) $params['algorithm']);
    }

    private static function resolveAlgorithmKey(string $raw): string
    {
        $key = strtolower(trim($raw));

        return match ($key) {
            'michelson', 'michelsoncontrast', 'michelson_contrast' => self::ALGORITHM_MICHELSON_CONTRAST,
            'weber', 'webercontrast', 'weber_contrast' => self::ALGORITHM_WEBER_CONTRAST,
            'rms', 'rmscontrast', 'rms_contrast' => self::ALGORITHM_RMS_CONTRAST,
            'deltae76', 'delta_e76', 'deltae', 'cie76', 'de76', 'lab' => self::ALGORITHM_DELTA_E76,
            'wcag', 'wcagcontrast', 'wcag_contrast', 'wcagcontrastratio', 'ratio', '' => self::ALGORITHM_WCAG_CONTRAST_RATIO,
            default => self::ALGORITHM_WCAG_CONTRAST_RATIO,
        };
    }

    /**
     * Return a human-readable label for a contrast value.
     */
    public static function getLabelForValue(float|string $value, ?string $algorithm = null): string
    {
        $v = is_numeric($value) ? (float) $value : 0.0;
        $algo = $algorithm !== null ? self::resolveAlgorithmKey($algorithm) : self::ALGORITHM_WCAG_CONTRAST_RATIO;

        return match ($algo) {
            self::ALGORITHM_WCAG_CONTRAST_RATIO => self::wcagLabel($v),
            self::ALGORITHM_DELTA_E76 => self::deltaE76Label($v),
            self::ALGORITHM_WEBER_CONTRAST => self::weberLabel($v),
            default => self::percentContrastLabel($v),
        };
    }

    private static function wcagContrastRatio(ColorInterface $color, ColorInterface $other): float
    {
        $l1 = self::relativeLuminance($color);
        $l2 = self::relativeLuminance($other);
        $high = max($l1, $l2);
        $low = min($l1, $l2);

        return (float) (($high + 0.05) / ($low + 0.05));
    }

    private static function michelsonContrast(ColorInterface $color, ColorInterface $other): float
    {
        $l1 = self::relativeLuminance($color);
        $l2 = self::relativeLuminance($other);
        $high = max($l1, $l2);
        $low = min($l1, $l2);
        $sum = $high + $low;
        if ($sum <= 0.0) {
            return 0.0;
        }

        return (($high - $low) / $sum) * 100.0;
    }

    private static function weberContrast(ColorInterface $color, ColorInterface $other): float
    {
        $target = self::relativeLuminance($color);
        $background = max(self::relativeLuminance($other), self::WEBER_L_MIN);

        return (($target - $background) / $background) * 100.0;
    }

    private static function rmsContrast(ColorInterface $color, ColorInterface $other): float
    {
        $l1 = self::relativeLuminance($color);
        $l2 = self::relativeLuminance($other);
        $denominator = sqrt(0.5 * ($l1 * $l1 + $l2 * $l2));
        if ($denominator <= 0.0) {
            return 0.0;
        }

        return min(100.0, (abs($l1 - $l2) / $denominator) * 100.0);
    }

    private static function deltaE76(ColorInterface $color, ColorInterface $other): float
    {
        $lab1 = $color->toLab();
        $lab2 = $other->toLab();
        $dl = $lab1->getChannel('l') - $lab2->getChannel('l');
        $da = $lab1->getChannel('a') - $lab2->getChannel('a');
        $db = $lab1->getChannel('b') - $lab2->getChannel('b');

        return sqrt($dl * $dl + $da * $da + $db * $db);
    }

    /**
     * @param string|ColorInterface $reference
     */
    private static function resolveReferenceColor(string|ColorInterface $reference): ColorInterface
    {
        if ($reference instanceof ColorInterface) {
            return $reference;
        }

        if ($reference === 'black') {
            return new Color(RGB::class, ['r' => 0, 'g' => 0, 'b' => 0]);
        }

        return new Color(RGB::class, ['r' => 255, 'g' => 255, 'b' => 255]);
    }

    public static function relativeLuminance(ColorInterface $color): float
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

    private static function wcagLabel(float $value): string
    {
        if ($value < 3) {
            return 'fails';
        }
        if ($value < 4.5) {
            return 'AA large';
        }
        if ($value < 7) {
            return 'AA';
        }

        return 'AAA';
    }

    private static function deltaE76Label(float $value): string
    {
        if ($value < 1.0) {
            return 'not perceptible';
        }
        if ($value < 2.0) {
            return 'small';
        }
        if ($value < 10.0) {
            return 'noticeable';
        }

        return 'large';
    }

    private static function weberLabel(float $value): string
    {
        $abs = abs($value);
        if ($abs < 10) {
            return 'low';
        }
        if ($abs < 50) {
            return 'moderate';
        }

        return 'high';
    }

    private static function percentContrastLabel(float $value): string
    {
        if ($value < 15) {
            return 'low';
        }
        if ($value < 40) {
            return 'moderate';
        }
        if ($value < 70) {
            return 'high';
        }

        return 'very high';
    }
}

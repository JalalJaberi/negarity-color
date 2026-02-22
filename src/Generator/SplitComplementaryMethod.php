<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

/**
 * Method used to compute split-complementary colors (base hue + 150° or + 210°).
 *
 * - Artistic: split-complementary in HSL (hue + 150° or 210°), common in design tools.
 * - Perceptual: split-complementary in LCh (hue + 150° or 210°), perceptually uniform.
 * - DisplayAccurate: split-complementary in HSV (hue + 150° or 210°), then convert to RGB.
 */
enum SplitComplementaryMethod: string
{
    case Artistic = 'artistic';
    case Perceptual = 'perceptual';
    case DisplayAccurate = 'display-accurate';

    /**
     * Resolve a value (enum, string, or null) to a SplitComplementaryMethod.
     */
    public static function fromValue(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }
        if ($value === null || $value === '') {
            return self::Perceptual;
        }
        $s = is_string($value) ? strtolower(str_replace([' ', '_'], ['-', '-'], $value)) : (string) $value;
        return match ($s) {
            'artistic' => self::Artistic,
            'perceptual' => self::Perceptual,
            'display-accurate', 'displayaccurate' => self::DisplayAccurate,
            default => self::Perceptual,
        };
    }
}

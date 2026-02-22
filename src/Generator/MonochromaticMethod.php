<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

/**
 * Method used to compute monochromatic variants (shades, tints, tones).
 *
 * - Artistic: HSL-based (lightness/saturation), common in design tools.
 * - Perceptual: LCh-based (L and C), perceptually uniform.
 * - DisplayAccurate: HSV-based (V and S), then convert to RGB.
 */
enum MonochromaticMethod: string
{
    case Artistic = 'artistic';
    case Perceptual = 'perceptual';
    case DisplayAccurate = 'display-accurate';

    /**
     * Resolve a value (enum, string, or null) to a MonochromaticMethod.
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

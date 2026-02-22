<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

/**
 * Method used to compute analogous colors (base hue ± 30° typically).
 *
 * - Artistic: analogous in HSL (hue + offset), common in design tools.
 * - Perceptual: analogous in LCh (hue + offset), perceptually uniform.
 * - DisplayAccurate: analogous in HSV (hue + offset), then convert to RGB.
 */
enum AnalogousMethod: string
{
    case Artistic = 'artistic';
    case Perceptual = 'perceptual';
    case DisplayAccurate = 'display-accurate';

    /**
     * Resolve a value (enum, string, or null) to an AnalogousMethod.
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

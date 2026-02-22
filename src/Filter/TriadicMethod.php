<?php

declare(strict_types=1);

namespace Negarity\Color\Filter;

/**
 * Method used to compute triadic colors (base hue + 120° or + 240°).
 *
 * - Artistic: triadic in HSL (hue + 120° or 240°), common in design tools.
 * - Perceptual: triadic in LCh (hue + 120° or 240°), perceptually uniform.
 * - DisplayAccurate: triadic in HSV (hue + 120° or 240°), then convert to RGB.
 */
enum TriadicMethod: string
{
    case Artistic = 'artistic';
    case Perceptual = 'perceptual';
    case DisplayAccurate = 'display-accurate';

    /**
     * Resolve a value (enum, string, or null) to a TriadicMethod.
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

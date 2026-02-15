<?php

declare(strict_types=1);

namespace Negarity\Color\Filter;

/**
 * Method used to compute the complementary color.
 *
 * - Artistic: complement in HSL (hue + 180°), common in design tools.
 * - Perceptual: complement in LCh (hue + 180°), perceptually uniform.
 * - DisplayAccurate: complement in RGB (invert R, G, B), matches screen display.
 */
enum ComplementMethod: string
{
    case Artistic = 'artistic';
    case Perceptual = 'perceptual';
    case DisplayAccurate = 'display-accurate';

    /**
     * Resolve a value (enum, string, or null) to a ComplementMethod.
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

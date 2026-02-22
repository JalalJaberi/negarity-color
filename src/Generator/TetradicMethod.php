<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

/**
 * Tetradic color scheme type: Square (4 colors 90° apart) or Rectangle (4 colors in a rectangle).
 *
 * - Square: base + 90°, + 180°, + 270° (evenly spaced on the wheel).
 * - Rectangle: base + 30°, + 180°, + 210° (two pairs of complements, rectangular spacing).
 */
enum TetradicMethod: string
{
    case Square = 'square';
    case Rectangle = 'rectangle';

    /**
     * Resolve a value (enum, string, or null) to a TetradicMethod. Default is Square.
     */
    public static function fromValue(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }
        if ($value === null || $value === '') {
            return self::Square;
        }
        $s = is_string($value) ? strtolower(str_replace([' ', '_'], ['', ''], $value)) : (string) $value;
        return match ($s) {
            'square' => self::Square,
            'rectangle', 'rect' => self::Rectangle,
            default => self::Square,
        };
    }
}

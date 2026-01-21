<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class LCh extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::LCH->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['l', 'c', 'h'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'l', 'c', 'h' => 0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in Lab color space.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['l', 'c', 'h'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'l' => static::assertRange((int)$value, 0, 100, $channel),
            'c' => is_numeric($value) ? null : throw new InvalidColorValueException("Channel '{$channel}' must be numeric."),
            'h' => static::assertRange((int)$value, 0, 360, $channel),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in color space '{static::getName()}'."),
        };
    }

    #[\Override]
    public static function supportsIlluminant(): bool
    {
        return true;
    }

    #[\Override]
    public static function supportsObserver(): bool
    {
        return true;
    }

    /**
     * Convert from LCh to RGB (via Lab and XYZ).
     * 
     * @param array<string, float|int> $values LCh values: ['l' => float, 'c' => float, 'h' => float]
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant (default: D65)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer (default: TwoDegree)
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function toRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $l = $values['l'] ?? 0;
        $c = $values['c'] ?? 0;
        $h = deg2rad($values['h'] ?? 0);

        // LCh -> Lab
        $a = cos($h) * $c;
        $b = sin($h) * $c;

        // Convert Lab to RGB
        return Lab::toRGB(['l' => $l, 'a' => $a, 'b' => $b], $illuminant, $observer);
    }

    /**
     * Convert from LCh to Lab (direct conversion).
     * 
     * @param array<string, float|int> $values LCh values
     * @return array<string, float> Lab values
     */
    public static function toLab(array $values): array
    {
        $l = $values['l'] ?? 0;
        $c = $values['c'] ?? 0;
        $h = deg2rad($values['h'] ?? 0);

        $a = cos($h) * $c;
        $b = sin($h) * $c;

        return ['l' => $l, 'a' => $a, 'b' => $b];
    }

    /**
     * Convert from LCh to XYZ (via Lab).
     * 
     * @param array<string, float|int> $values LCh values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, float> XYZ values
     */
    public static function toXYZ(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $lab = static::toLab($values);
        return Lab::toXYZ($lab, $illuminant, $observer);
    }

    /**
     * Convert from LCh to CMYK (via RGB).
     * 
     * @param array<string, float|int> $values LCh values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, int> CMYK values
     */
    public static function toCMYK(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $rgb = static::toRGB($values, $illuminant, $observer);
        return CMYK::fromRGB($rgb);
    }

    /**
     * Convert from LCh to HSL (via RGB).
     * 
     * @param array<string, float|int> $values LCh values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, int> HSL values
     */
    public static function toHSL(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $rgb = static::toRGB($values, $illuminant, $observer);
        return HSL::fromRGB($rgb);
    }

    /**
     * Convert from LCh to HSLA (via RGB and HSL).
     * 
     * @param array<string, float|int> $values LCh values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, int> HSLA values
     */
    public static function toHSLA(
        array $values,
        int $alpha = 255,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $hsl = static::toHSL($values, $illuminant, $observer);
        return [
            'h' => $hsl['h'],
            's' => $hsl['s'],
            'l' => $hsl['l'],
            'a' => $alpha
        ];
    }

    /**
     * Convert from LCh to HSV (via RGB).
     * 
     * @param array<string, float|int> $values LCh values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, int> HSV values
     */
    public static function toHSV(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $rgb = static::toRGB($values, $illuminant, $observer);
        return HSV::fromRGB($rgb);
    }

    /**
     * Convert from LCh to RGBA (via RGB).
     * 
     * @param array<string, float|int> $values LCh values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, int> RGBA values
     */
    public static function toRGBA(
        array $values,
        int $alpha = 255,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $rgb = static::toRGB($values, $illuminant, $observer);
        return RGBA::fromRGB($rgb, $alpha);
    }

    /**
     * Convert from LCh to YCbCr (via RGB).
     * 
     * @param array<string, float|int> $values LCh values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, float|int> YCbCr values
     */
    public static function toYCbCr(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $rgb = static::toRGB($values, $illuminant, $observer);
        return YCbCr::fromRGB($rgb);
    }

    /**
     * Convert from RGB to LCh (via Lab).
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant (default: D65)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer (default: TwoDegree)
     * @return array<string, float> LCh values: ['l' => float, 'c' => float, 'h' => float]
     */
    public static function fromRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        // First convert RGB to Lab
        $lab = \Negarity\Color\ColorSpace\Lab::fromRGB($values, $illuminant, $observer);
        $l = $lab['l'];
        $a = $lab['a'];
        $b = $lab['b'];
        
        // Then convert Lab to LCh
        $c = sqrt($a * $a + $b * $b);
        $h = rad2deg(atan2($b, $a));
        if ($h < 0) {
            $h += 360;
        }
        
        return ['l' => $l, 'c' => $c, 'h' => $h];
    }
}

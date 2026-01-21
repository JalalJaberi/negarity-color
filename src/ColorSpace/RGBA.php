<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class RGBA extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::RGBA->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['r', 'g', 'b', 'a'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'r', 'g', 'b' => 0,
            'a' => 255,
            default => throw new InvalidColorValueException("Channel '{$name}' does not exist in RGBA color space."),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['r', 'g', 'b', 'a'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'r', 'g', 'b' => static::assertRange((int)$value, 0, 255, $channel),
            'a' => static::assertRange((int)$value, 0, 255, $channel),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in RGBA color space."),
        };
    }

    /**
     * Convert from RGBA to RGB (ignores alpha channel).
     * 
     * @param array<string, float|int> $values RGBA values: ['r' => int, 'g' => int, 'b' => int, 'a' => int]
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function toRGB(array $values): array
    {
        return [
            'r' => (int) ($values['r'] ?? 0),
            'g' => (int) ($values['g'] ?? 0),
            'b' => (int) ($values['b'] ?? 0)
        ];
    }

    /**
     * Convert from RGBA to CMYK (via RGB).
     * 
     * @param array<string, float|int> $values RGBA values
     * @return array<string, int> CMYK values
     */
    public static function toCMYK(array $values): array
    {
        $rgb = static::toRGB($values);
        return CMYK::fromRGB($rgb);
    }

    /**
     * Convert from RGBA to HSL (via RGB).
     * 
     * @param array<string, float|int> $values RGBA values
     * @return array<string, int> HSL values
     */
    public static function toHSL(array $values): array
    {
        $rgb = static::toRGB($values);
        return HSL::fromRGB($rgb);
    }

    /**
     * Convert from RGBA to HSLA (via RGB and HSL, preserves alpha).
     * 
     * @param array<string, float|int> $values RGBA values
     * @return array<string, int> HSLA values
     */
    public static function toHSLA(array $values): array
    {
        $hsl = static::toHSL($values);
        return [
            'h' => $hsl['h'],
            's' => $hsl['s'],
            'l' => $hsl['l'],
            'a' => (int) ($values['a'] ?? 255)
        ];
    }

    /**
     * Convert from RGBA to HSV (via RGB).
     * 
     * @param array<string, float|int> $values RGBA values
     * @return array<string, int> HSV values
     */
    public static function toHSV(array $values): array
    {
        $rgb = static::toRGB($values);
        return HSV::fromRGB($rgb);
    }

    /**
     * Convert from RGBA to Lab (via RGB and XYZ).
     * 
     * @param array<string, float|int> $values RGBA values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, float> Lab values
     */
    public static function toLab(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $rgb = static::toRGB($values);
        return Lab::fromRGB($rgb, $illuminant, $observer);
    }

    /**
     * Convert from RGBA to LCh (via RGB, XYZ, and Lab).
     * 
     * @param array<string, float|int> $values RGBA values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, float> LCh values
     */
    public static function toLCh(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $rgb = static::toRGB($values);
        return LCh::fromRGB($rgb, $illuminant, $observer);
    }

    /**
     * Convert from RGBA to XYZ (via RGB).
     * 
     * @param array<string, float|int> $values RGBA values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, float> XYZ values
     */
    public static function toXYZ(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $rgb = static::toRGB($values);
        return XYZ::fromRGB($rgb, $illuminant, $observer);
    }

    /**
     * Convert from RGBA to YCbCr (via RGB).
     * 
     * @param array<string, float|int> $values RGBA values
     * @return array<string, float|int> YCbCr values
     */
    public static function toYCbCr(array $values): array
    {
        $rgb = static::toRGB($values);
        return YCbCr::fromRGB($rgb);
    }

    /**
     * Convert from RGB to RGBA (alpha defaults to 255).
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param int $alpha Alpha channel value (0-255, default: 255)
     * @return array<string, int> RGBA values: ['r' => int, 'g' => int, 'b' => int, 'a' => int]
     */
    public static function fromRGB(array $values, int $alpha = 255): array
    {
        return [
            'r' => (int) ($values['r'] ?? 0),
            'g' => (int) ($values['g'] ?? 0),
            'b' => (int) ($values['b'] ?? 0),
            'a' => $alpha
        ];
    }
}

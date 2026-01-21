<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class HSLA extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::HSLA->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['h', 's', 'l', 'a'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'h', 's', 'l' => 0,
            'a' => 255,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSLA color space.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['h', 's', 'l', 'a'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'h' => static::assertRange((int)$value, 0, 360, $channel),
            's', 'l' => static::assertRange((int)$value, 0, 100, $channel),
            'a' => static::assertRange((int)$value, 0, 255, $channel),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSLA color space.', $channel)),
        };
    }

    /**
     * Convert from HSLA to RGB (ignores alpha channel).
     * 
     * @param array<string, float|int> $values HSLA values: ['h' => int, 's' => int, 'l' => int, 'a' => int]
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function toRGB(array $values): array
    {
        // Use HSL conversion (ignore alpha)
        return HSL::toRGB($values);
    }

    /**
     * Convert from HSLA to HSL (removes alpha channel).
     * 
     * @param array<string, float|int> $values HSLA values
     * @return array<string, int> HSL values
     */
    public static function toHSL(array $values): array
    {
        return [
            'h' => (int) ($values['h'] ?? 0),
            's' => (int) ($values['s'] ?? 0),
            'l' => (int) ($values['l'] ?? 0)
        ];
    }

    /**
     * Convert from HSLA to CMYK (via RGB).
     * 
     * @param array<string, float|int> $values HSLA values
     * @return array<string, int> CMYK values
     */
    public static function toCMYK(array $values): array
    {
        $rgb = static::toRGB($values);
        return CMYK::fromRGB($rgb);
    }

    /**
     * Convert from HSLA to HSV (via HSL).
     * 
     * @param array<string, float|int> $values HSLA values
     * @return array<string, int> HSV values
     */
    public static function toHSV(array $values): array
    {
        $hsl = static::toHSL($values);
        return HSL::toHSV($hsl);
    }

    /**
     * Convert from HSLA to RGBA (via RGB, preserves alpha).
     * 
     * @param array<string, float|int> $values HSLA values
     * @return array<string, int> RGBA values
     */
    public static function toRGBA(array $values): array
    {
        $rgb = static::toRGB($values);
        return RGBA::fromRGB($rgb, (int) ($values['a'] ?? 255));
    }

    /**
     * Convert from HSLA to Lab (via RGB and XYZ).
     * 
     * @param array<string, float|int> $values HSLA values
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
     * Convert from HSLA to LCh (via RGB, XYZ, and Lab).
     * 
     * @param array<string, float|int> $values HSLA values
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
     * Convert from HSLA to XYZ (via RGB).
     * 
     * @param array<string, float|int> $values HSLA values
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
     * Convert from HSLA to YCbCr (via RGB).
     * 
     * @param array<string, float|int> $values HSLA values
     * @return array<string, float|int> YCbCr values
     */
    public static function toYCbCr(array $values): array
    {
        $rgb = static::toRGB($values);
        return YCbCr::fromRGB($rgb);
    }

    /**
     * Convert from RGB to HSLA (alpha defaults to 255).
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param int $alpha Alpha channel value (0-255, default: 255)
     * @return array<string, int> HSLA values: ['h' => int, 's' => int, 'l' => int, 'a' => int]
     */
    public static function fromRGB(array $values, int $alpha = 255): array
    {
        $hsl = HSL::fromRGB($values);
        return [
            'h' => $hsl['h'],
            's' => $hsl['s'],
            'l' => $hsl['l'],
            'a' => $alpha
        ];
    }
}

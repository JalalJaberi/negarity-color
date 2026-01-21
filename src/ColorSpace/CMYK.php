<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class CMYK extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::CMYK->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['c', 'm', 'y', 'k'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'c', 'm', 'y', 'k' => 0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in CMYK color space.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['c', 'm', 'y', 'k'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'c', 'm', 'y', 'k' => static::assertRange((int)$value, 0, 100, $channel),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in CMYK color space.', $channel)),
        };
    }

    /**
     * Convert from CMYK to RGB.
     * 
     * @param array<string, float|int> $values CMYK values: ['c' => int, 'm' => int, 'y' => int, 'k' => int]
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function toRGB(array $values): array
    {
        $c = ($values['c'] ?? 0) / 100;
        $m = ($values['m'] ?? 0) / 100;
        $y = ($values['y'] ?? 0) / 100;
        $k = ($values['k'] ?? 0) / 100;
        
        $r = 255 * (1 - $c) * (1 - $k);
        $g = 255 * (1 - $m) * (1 - $k);
        $b = 255 * (1 - $y) * (1 - $k);
        
        return [
            'r' => (int) round($r),
            'g' => (int) round($g),
            'b' => (int) round($b)
        ];
    }

    /**
     * Convert from CMYK to HSL (via RGB).
     * 
     * @param array<string, float|int> $values CMYK values
     * @return array<string, int> HSL values
     */
    public static function toHSL(array $values): array
    {
        $rgb = static::toRGB($values);
        return HSL::fromRGB($rgb);
    }

    /**
     * Convert from CMYK to HSLA (via RGB).
     * 
     * @param array<string, float|int> $values CMYK values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @return array<string, int> HSLA values
     */
    public static function toHSLA(array $values, int $alpha = 255): array
    {
        $rgb = static::toRGB($values);
        return HSLA::fromRGB($rgb, $alpha);
    }

    /**
     * Convert from CMYK to HSV (via RGB).
     * 
     * @param array<string, float|int> $values CMYK values
     * @return array<string, int> HSV values
     */
    public static function toHSV(array $values): array
    {
        $rgb = static::toRGB($values);
        return HSV::fromRGB($rgb);
    }

    /**
     * Convert from CMYK to RGBA (via RGB).
     * 
     * @param array<string, float|int> $values CMYK values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @return array<string, int> RGBA values
     */
    public static function toRGBA(array $values, int $alpha = 255): array
    {
        $rgb = static::toRGB($values);
        return RGBA::fromRGB($rgb, $alpha);
    }

    /**
     * Convert from CMYK to Lab (via RGB and XYZ).
     * 
     * @param array<string, float|int> $values CMYK values
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
     * Convert from CMYK to LCh (via RGB, XYZ, and Lab).
     * 
     * @param array<string, float|int> $values CMYK values
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
     * Convert from CMYK to XYZ (via RGB).
     * 
     * @param array<string, float|int> $values CMYK values
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
     * Convert from CMYK to YCbCr (via RGB).
     * 
     * @param array<string, float|int> $values CMYK values
     * @return array<string, float|int> YCbCr values
     */
    public static function toYCbCr(array $values): array
    {
        $rgb = static::toRGB($values);
        return YCbCr::fromRGB($rgb);
    }

    /**
     * Convert from RGB to CMYK.
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @return array<string, int> CMYK values: ['c' => int, 'm' => int, 'y' => int, 'k' => int]
     */
    public static function fromRGB(array $values): array
    {
        $r = ($values['r'] ?? 0) / 255;
        $g = ($values['g'] ?? 0) / 255;
        $b = ($values['b'] ?? 0) / 255;

        $k = 1 - max($r, $g, $b);
        if ($k == 1) {
            return ['c' => 0, 'm' => 0, 'y' => 0, 'k' => 100];
        }

        $c = max(0, min(1, (1 - $r - $k) / (1 - $k)));
        $m = max(0, min(1, (1 - $g - $k) / (1 - $k)));
        $y = max(0, min(1, (1 - $b - $k) / (1 - $k)));

        return [
            'c' => (int) round($c * 100),
            'm' => (int) round($m * 100),
            'y' => (int) round($y * 100),
            'k' => (int) round($k * 100)
        ];
    }
}

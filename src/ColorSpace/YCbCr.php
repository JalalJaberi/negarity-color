<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class YCbCr extends AbstractColorSpace
{
    public static function getName(): string
    {
        return ColorSpaceEnum::YCBCR->value;
    }

    public static function getChannels(): array
    {
        return ['y', 'cb', 'cr'];
    }

    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'y', 'cb', 'cr' => 0.0,
            default => throw new InvalidColorValueException("Channel '{$name}' does not exist in color space 'ycbcr'."),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['y', 'cb', 'cr'], true);
    }

    public static function validateValue(string $channel, float $value): void
    {
        match ($channel) {
            'y' => static::assertRange($value, 0.0, 100.0, $channel),
            'cb', 'cr' => static::assertRange($value, -128.0, 127.0, $channel),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in color space 'ycbcr'."),
        };
    }

    /**
     * Convert from YCbCr to RGB.
     * 
     * @param array<string, float|int> $values YCbCr values: ['y' => float, 'cb' => int, 'cr' => int]
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (ignored for YCbCr)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (ignored for YCbCr)
     * @return array<string, float> RGB values: ['r' => float, 'g' => float, 'b' => float]
     */
    public static function toRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array
    {
        $y = $values['y'] ?? 0;
        $cb = $values['cb'] ?? 0;
        $cr = $values['cr'] ?? 0;

        // Scale Y to 0-255
        $yScaled = $y * 255 / 100;

        // Convert to RGB using standard formula for centered Cb/Cr
        $r = $yScaled + 1.402 * $cr;
        $g = $yScaled - 0.344136 * $cb - 0.714136 * $cr;
        $b = $yScaled + 1.772 * $cb;

        // Clamp RGB values to 0-255
        return [
            'r' => max(0.0, min(255.0, $r)),
            'g' => max(0.0, min(255.0, $g)),
            'b' => max(0.0, min(255.0, $b))
        ];
    }

    /**
     * Convert from YCbCr to CMYK (via RGB).
     * 
     * @param array<string, float|int> $values YCbCr values
     * @return array<string, int> CMYK values
     */
    public static function toCMYK(array $values): array
    {
        $rgb = static::toRGB($values);
        return CMYK::fromRGB($rgb);
    }

    /**
     * Convert from YCbCr to HSL (via RGB).
     * 
     * @param array<string, float|int> $values YCbCr values
     * @return array<string, int> HSL values
     */
    public static function toHSL(array $values): array
    {
        $rgb = static::toRGB($values);
        return HSL::fromRGB($rgb);
    }

    /**
     * Convert from YCbCr to HSLA (via RGB and HSL).
     * 
     * @param array<string, float|int> $values YCbCr values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @return array<string, int> HSLA values
     */
    public static function toHSLA(array $values, int $alpha = 255): array
    {
        $hsl = static::toHSL($values);
        return [
            'h' => $hsl['h'],
            's' => $hsl['s'],
            'l' => $hsl['l'],
            'a' => $alpha
        ];
    }

    /**
     * Convert from YCbCr to HSV (via RGB).
     * 
     * @param array<string, float|int> $values YCbCr values
     * @return array<string, int> HSV values
     */
    public static function toHSV(array $values): array
    {
        $rgb = static::toRGB($values);
        return HSV::fromRGB($rgb);
    }

    /**
     * Convert from YCbCr to RGBA (via RGB).
     * 
     * @param array<string, float|int> $values YCbCr values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @return array<string, int> RGBA values
     */
    public static function toRGBA(array $values, int $alpha = 255): array
    {
        $rgb = static::toRGB($values);
        return RGBA::fromRGB($rgb, $alpha);
    }

    /**
     * Convert from YCbCr to Lab (via RGB and XYZ).
     * 
     * @param array<string, float|int> $values YCbCr values
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
        return Lab::fromRGB($rgb, 255, $illuminant, $observer);
    }

    /**
     * Convert from YCbCr to LCh (via RGB, XYZ, and Lab).
     * 
     * @param array<string, float|int> $values YCbCr values
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
        return LCh::fromRGB($rgb, 255, $illuminant, $observer);
    }

    /**
     * Convert from YCbCr to XYZ (via RGB).
     * 
     * @param array<string, float|int> $values YCbCr values
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
        return XYZ::fromRGB($rgb, 255, $illuminant, $observer);
    }

    /**
     * Convert from RGB to YCbCr.
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param int $alpha Optional alpha channel (ignored for YCbCr)
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (ignored for YCbCr)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (ignored for YCbCr)
     * @return array<string, float> YCbCr values: ['y' => float, 'cb' => float, 'cr' => float]
     */
    public static function fromRGB(
        array $values,
        int $alpha = 255,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array
    {
        $r = $values['r'] ?? 0;
        $g = $values['g'] ?? 0;
        $b = $values['b'] ?? 0;

        // Compute Y, Cb, Cr using standard linear formula
        // Using 8-bit RGB (0-255)
        $y  = 0.299 * $r + 0.587 * $g + 0.114 * $b;
        $cb = -0.168736 * $r - 0.331264 * $g + 0.5 * $b;
        $cr = 0.5 * $r - 0.418688 * $g - 0.081312 * $b;

        // Adjust ranges: Y: 0-100, Cb/Cr: -128 → 127
        $y  = $y * 100 / 255;   // scale Y to 0-100
        // Cb/Cr are already centered at 0, no +128 offset

        return [
            'y' => max(0.0, min(100.0, $y)),
            'cb' => max(-128.0, min(127.0, $cb)),
            'cr' => max(-128.0, min(127.0, $cr))
        ];
    }
}

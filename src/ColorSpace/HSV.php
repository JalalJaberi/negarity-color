<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class HSV extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::HSV->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['h', 's', 'v'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'h', 's', 'v' => 0.0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSV color space.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['h', 's', 'v'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, float $value): void
    {
        match ($channel) {
            'h' => static::assertRange($value, 0.0, 360.0, $channel),
            's', 'v' => static::assertRange($value, 0.0, 100.0, $channel),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSV color space.', $channel)),
        };
    }

    /**
     * Convert from HSV to RGB.
     * 
     * @param array<string, float|int> $values HSV values: ['h' => int, 's' => int, 'v' => int]
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (ignored for HSV)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (ignored for HSV)
     * @return array<string, float> RGB values: ['r' => float, 'g' => float, 'b' => float]
     */
    public static function toRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array
    {
        $h = fmod(($values['h'] ?? 0), 360);
        $s = ($values['s'] ?? 0) / 100;
        $v = ($values['v'] ?? 0) / 100;

        $c = $v * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $v - $c;

        $r = $g = $b = 0;

        if ($h < 60) {
            $r = $c; $g = $x; $b = 0;
        } elseif ($h < 120) {
            $r = $x; $g = $c; $b = 0;
        } elseif ($h < 180) {
            $r = 0; $g = $c; $b = $x;
        } elseif ($h < 240) {
            $r = 0; $g = $x; $b = $c;
        } elseif ($h < 300) {
            $r = $x; $g = 0; $b = $c;
        } else {
            $r = $c; $g = 0; $b = $x;
        }

        $r = max(0.0, min(255.0, ($r + $m) * 255));
        $g = max(0.0, min(255.0, ($g + $m) * 255));
        $b = max(0.0, min(255.0, ($b + $m) * 255));

        return [
            'r' => $r,
            'g' => $g,
            'b' => $b
        ];
    }

    /**
     * Convert from HSV to CMYK (via RGB).
     * 
     * @param array<string, float|int> $values HSV values
     * @return array<string, int> CMYK values
     */
    public static function toCMYK(array $values): array
    {
        $rgb = static::toRGB($values);
        return CMYK::fromRGB($rgb);
    }

    /**
     * Convert from HSV to HSL (direct conversion).
     * 
     * @param array<string, float|int> $values HSV values
     * @return array<string, int> HSL values
     */
    public static function toHSL(array $values): array
    {
        $h = $values['h'] ?? 0;
        $s = ($values['s'] ?? 0) / 100;
        $v = ($values['v'] ?? 0) / 100;

        $l = $v * (1 - $s / 2);
        $sNew = ($l == 0 || $l == 1) ? 0 : ($v - $l) / min($l, 1 - $l);

        return [
            'h' => (int) round($h),
            's' => (int) round($sNew * 100),
            'l' => (int) round($l * 100)
        ];
    }

    /**
     * Convert from HSV to HSLA (via HSL).
     * 
     * @param array<string, float|int> $values HSV values
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
     * Convert from HSV to RGBA (via RGB).
     * 
     * @param array<string, float|int> $values HSV values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @return array<string, int> RGBA values
     */
    public static function toRGBA(array $values, int $alpha = 255): array
    {
        $rgb = static::toRGB($values);
        return RGBA::fromRGB($rgb, $alpha);
    }

    /**
     * Convert from HSV to Lab (via RGB and XYZ).
     * 
     * @param array<string, float|int> $values HSV values
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
     * Convert from HSV to LCh (via RGB, XYZ, and Lab).
     * 
     * @param array<string, float|int> $values HSV values
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
     * Convert from HSV to XYZ (via RGB).
     * 
     * @param array<string, float|int> $values HSV values
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
     * Convert from HSV to YCbCr (via RGB).
     * 
     * @param array<string, float|int> $values HSV values
     * @return array<string, float|int> YCbCr values
     */
    public static function toYCbCr(array $values): array
    {
        $rgb = static::toRGB($values);
        return YCbCr::fromRGB($rgb);
    }

    /**
     * Convert from RGB to HSV.
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param int $alpha Optional alpha channel (ignored for HSV)
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (ignored for HSV)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (ignored for HSV)
     * @return array<string, float> HSV values: ['h' => float, 's' => float, 'v' => float]
     */
    public static function fromRGB(
        array $values,
        int $alpha = 255,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array
    {
        $r = ($values['r'] ?? 0) / 255;
        $g = ($values['g'] ?? 0) / 255;
        $b = ($values['b'] ?? 0) / 255;
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $v = $max;
        
        $d = $max - $min;
        $s = ($max == 0.0) ? 0.0 : $d / $max;
        
        $h = 0.0;
        
        if ($d != 0.0) {
            if ($max === $r) {
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
            } elseif ($max === $g) {
                $h = ($b - $r) / $d + 2;
            } else {
                $h = ($r - $g) / $d + 4;
            }
            $h /= 6;
        }
        
        $h = fmod($h * 360, 360);
        $s = max(0.0, min(100.0, $s * 100));
        $v = max(0.0, min(100.0, $v * 100));
        
        return [
            'h' => $h,
            's' => $s,
            'v' => $v
        ];
    }
}

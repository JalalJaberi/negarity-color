<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class HSL extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::HSL->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['h', 's', 'l'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'h', 's', 'l' => 0.0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSL color space.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['h', 's', 'l'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, float $value): void
    {
        match ($channel) {
            'h' => static::assertRange($value, 0.0, 360.0, $channel),
            's', 'l' => static::assertRange($value, 0.0, 100.0, $channel),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSL color space.', $channel)),
        };
    }

    #[\Override]
    public static function clampValue(string $channel, float $value): float
    {
        return match ($channel) {
            'h' => static::clampRange($value, 0.0, 360.0),
            's', 'l' => static::clampRange($value, 0.0, 100.0),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSL color space.', $channel)),
        };
    }

    /**
     * Convert from HSL to RGB.
     * 
     * @param array<string, float|int> $values HSL values: ['h' => int, 's' => int, 'l' => int]
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (ignored for HSL)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (ignored for HSL)
     * @return array<string, float> RGB values: ['r' => float, 'g' => float, 'b' => float]
     */
    public static function toRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array
    {
        $h = fmod(($values['h'] ?? 0), 360) / 60;
        $s = ($values['s'] ?? 0) / 100;
        $l = ($values['l'] ?? 0) / 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h, 2) - 1));
        $m = $l - $c / 2;

        $r = $g = $b = 0;

        if ($h < 1) {
            $r = $c;
            $g = $x;
        } elseif ($h < 2) {
            $r = $x;
            $g = $c;
        } elseif ($h < 3) {
            $g = $c;
            $b = $x;
        } elseif ($h < 4) {
            $g = $x;
            $b = $c;
        } elseif ($h < 5) {
            $r = $x;
            $b = $c;
        } else {
            $r = $c;
            $b = $x;
        }

        $r = ($r + $m) * 255;
        $g = ($g + $m) * 255;
        $b = ($b + $m) * 255;

        $r = max(0.0, min(255.0, $r));
        $g = max(0.0, min(255.0, $g));
        $b = max(0.0, min(255.0, $b));

        return [
            'r' => $r,
            'g' => $g,
            'b' => $b
        ];
    }

    /**
     * Convert from HSL to CMYK (via RGB).
     * 
     * @param array<string, float|int> $values HSL values
     * @return array<string, int> CMYK values
     */
    public static function toCMYK(array $values): array
    {
        $rgb = static::toRGB($values);
        return CMYK::fromRGB($rgb);
    }

    /**
     * Convert from HSL to HSLA (adds alpha channel).
     * 
     * @param array<string, float|int> $values HSL values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @return array<string, int> HSLA values
     */
    public static function toHSLA(array $values, int $alpha = 255): array
    {
        return [
            'h' => (int) ($values['h'] ?? 0),
            's' => (int) ($values['s'] ?? 0),
            'l' => (int) ($values['l'] ?? 0),
            'a' => $alpha
        ];
    }

    /**
     * Convert from HSL to HSV (direct conversion).
     * 
     * @param array<string, float|int> $values HSL values
     * @return array<string, int> HSV values
     */
    public static function toHSV(array $values): array
    {
        $h = $values['h'] ?? 0;
        $s = ($values['s'] ?? 0) / 100;
        $l = ($values['l'] ?? 0) / 100;

        $v = $l + $s * min($l, 1 - $l);
        $sNew = ($v == 0) ? 0 : 2 * (1 - $l / $v);

        return [
            'h' => (int) round($h),
            's' => (int) round($sNew * 100),
            'v' => (int) round($v * 100)
        ];
    }

    /**
     * Convert from HSL to RGBA (via RGB).
     * 
     * @param array<string, float|int> $values HSL values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @return array<string, int> RGBA values
     */
    public static function toRGBA(array $values, int $alpha = 255): array
    {
        $rgb = static::toRGB($values);
        return RGBA::fromRGB($rgb, $alpha);
    }

    /**
     * Convert from HSL to Lab (via RGB and XYZ).
     * 
     * @param array<string, float|int> $values HSL values
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
     * Convert from HSL to LCh (via RGB, XYZ, and Lab).
     * 
     * @param array<string, float|int> $values HSL values
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
     * Convert from HSL to XYZ (via RGB).
     * 
     * @param array<string, float|int> $values HSL values
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
     * Convert from HSL to YCbCr (via RGB).
     * 
     * @param array<string, float|int> $values HSL values
     * @return array<string, float|int> YCbCr values
     */
    public static function toYCbCr(array $values): array
    {
        $rgb = static::toRGB($values);
        return YCbCr::fromRGB($rgb);
    }

    /**
     * Convert from RGB to HSL.
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param int $alpha Optional alpha channel (ignored for HSL)
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (ignored for HSL)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (ignored for HSL)
     * @return array<string, float> HSL values: ['h' => float, 's' => float, 'l' => float]
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
        $l = ($max + $min) / 2;
        
        $h = 0;
        $s = 0;
        $d = $max - $min;
        
        if ($d != 0.0) {
            $s = ($l > 0.5)
                ? $d / (2 - $max - $min)
                : $d / ($max + $min);
        
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
        $l = max(0.0, min(100.0, $l * 100));
        
        return [
            'h' => $h,
            's' => $s,
            'l' => $l
        ];
    }
}

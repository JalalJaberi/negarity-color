<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class XYZ extends AbstractColorSpace
{
    public static function getName(): string
    {
        return ColorSpaceEnum::XYZ->value;
    }

    public static function getChannels(): array
    {
        return ['x', 'y', 'z'];
    }

    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'x', 'y', 'z' => 0.0,
            default => throw new InvalidColorValueException("Channel '{$name}' does not exist in color space 'xyz'."),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['x', 'y', 'z'], true);
    }

    public static function validateValue(string $channel, float $value): void
    {
        if (!is_numeric($value)) {
            throw new InvalidColorValueException("Channel '{$channel}' must be numeric.");
        }
    }

    #[\Override]
    public static function clampValue(string $channel, float $value): float
    {
        // XYZ channels don't have strict ranges, so return as-is
        if (!in_array($channel, ['x', 'y', 'z'], true)) {
            throw new InvalidColorValueException("Channel '{$channel}' does not exist in color space 'xyz'.");
        }
        return $value;
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
     * Convert from XYZ to RGB.
     * 
     * @param array<string, float|int> $values XYZ values: ['x' => float, 'y' => float, 'z' => float]
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant (default: D65)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer (default: TwoDegree)
     * @return array<string, float> RGB values: ['r' => float, 'g' => float, 'b' => float]
     */
    public static function toRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $x = (($values['x'] ?? 0) / 100);
        $y = (($values['y'] ?? 0) / 100);
        $z = (($values['z'] ?? 0) / 100);

        // sRGB D65 conversion matrix (linear)
        $matrix = [
            [3.2404542, -1.5371385, -0.4985314],
            [-0.9692660, 1.8760108, 0.0415560],
            [0.0556434, -0.2040259, 1.0572252]
        ];

        $r = $x * $matrix[0][0] + $y * $matrix[0][1] + $z * $matrix[0][2];
        $g = $x * $matrix[1][0] + $y * $matrix[1][1] + $z * $matrix[1][2];
        $b = $x * $matrix[2][0] + $y * $matrix[2][1] + $z * $matrix[2][2];

        // Apply gamma correction
        $rgb = [$r, $g, $b];
        foreach ($rgb as &$val) {
            if ($val <= 0.0031308) {
                $val = 12.92 * $val;
            } else {
                $val = 1.055 * pow($val, 1 / 2.4) - 0.055;
            }
            $val = $val * 255;
        }
        unset($val);

        // Clamp RGB values to 0-255 (XYZ has wider gamut than sRGB)
        return [
            'r' => max(0.0, min(255.0, $rgb[0])),
            'g' => max(0.0, min(255.0, $rgb[1])),
            'b' => max(0.0, min(255.0, $rgb[2]))
        ];
    }

    /**
     * Convert from XYZ to CMYK (via RGB).
     * 
     * @param array<string, float|int> $values XYZ values
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
     * Convert from XYZ to HSL (via RGB).
     * 
     * @param array<string, float|int> $values XYZ values
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
     * Convert from XYZ to HSLA (via RGB and HSL).
     * 
     * @param array<string, float|int> $values XYZ values
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
     * Convert from XYZ to HSV (via RGB).
     * 
     * @param array<string, float|int> $values XYZ values
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
     * Convert from XYZ to RGBA (via RGB).
     * 
     * @param array<string, float|int> $values XYZ values
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
     * Convert from XYZ to Lab (direct conversion).
     * 
     * @param array<string, float|int> $values XYZ values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, float> Lab values
     */
    public static function toLab(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $illuminant = $illuminant ?? \Negarity\Color\CIE\CIEIlluminant::D65;
        $observer = $observer ?? \Negarity\Color\CIE\CIEObserver::TwoDegree;
        $refWhite = \Negarity\Color\CIE\CIEIlluminantData::getXYZ($illuminant, $observer);
        
        // Normalize XYZ using reference white
        $x = (($values['x'] ?? 0) / 100) / ($refWhite['x'] / 100);
        $y = (($values['y'] ?? 0) / 100) / ($refWhite['y'] / 100);
        $z = (($values['z'] ?? 0) / 100) / ($refWhite['z'] / 100);
        
        $delta = 6/29;
        
        $fx = ($x > $delta**3) ? pow($x, 1/3) : ($x/(3*$delta*$delta) + 4/29);
        $fy = ($y > $delta**3) ? pow($y, 1/3) : ($y/(3*$delta*$delta) + 4/29);
        $fz = ($z > $delta**3) ? pow($z, 1/3) : ($z/(3*$delta*$delta) + 4/29);
        
        $l = 116*$fy - 16;
        $a = 500*($fx - $fy);
        $b = 200*($fy - $fz);
        
        return ['l' => $l, 'a' => $a, 'b' => $b];
    }

    /**
     * Convert from XYZ to LCh (via Lab).
     * 
     * @param array<string, float|int> $values XYZ values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, float> LCh values
     */
    public static function toLCh(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $lab = static::toLab($values, $illuminant, $observer);
        $l = $lab['l'];
        $a = $lab['a'];
        $b = $lab['b'];
        
        $c = sqrt($a * $a + $b * $b);
        $h = rad2deg(atan2($b, $a));
        if ($h < 0) {
            $h += 360;
        }
        
        return ['l' => $l, 'c' => $c, 'h' => $h];
    }

    /**
     * Convert from XYZ to YCbCr (via RGB).
     * 
     * @param array<string, float|int> $values XYZ values
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
     * Convert from RGB to XYZ.
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param int $alpha Optional alpha channel (ignored for XYZ)
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant (default: D65)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer (default: TwoDegree)
     * @return array<string, float> XYZ values: ['x' => float, 'y' => float, 'z' => float]
     */
    public static function fromRGB(
        array $values,
        int $alpha = 255,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $r = ($values['r'] ?? 0) / 255;
        $g = ($values['g'] ?? 0) / 255;
        $b = ($values['b'] ?? 0) / 255;

        // Inverse gamma (linearization)
        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Linear RGB → XYZ
        $matrix = [
            [0.4124564, 0.3575761, 0.1804375],
            [0.2126729, 0.7151522, 0.0721750],
            [0.0193339, 0.1191920, 0.9503041]
        ];

        $x = $r * $matrix[0][0] + $g * $matrix[0][1] + $b * $matrix[0][2];
        $y = $r * $matrix[1][0] + $g * $matrix[1][1] + $b * $matrix[1][2];
        $z = $r * $matrix[2][0] + $g * $matrix[2][1] + $b * $matrix[2][2];

        // Scale to 0–100 (percentage)
        return [
            'x' => round($x * 100, 4),
            'y' => round($y * 100, 4),
            'z' => round($z * 100, 4)
        ];
    }
}

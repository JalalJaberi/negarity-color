<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class Lab extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::LAB->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['l', 'a', 'b'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'l', 'a', 'b' => 0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in Lab color space.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['l', 'a', 'b'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'l' => static::assertRange((int)$value, 0, 100, $channel),
            'a', 'b' => is_numeric($value) ? null : throw new InvalidColorValueException("Channel '{$channel}' must be numeric."),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in Lab color space.', $channel)),
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
     * Convert from Lab to RGB (via XYZ).
     * 
     * @param array<string, float|int> $values Lab values: ['l' => float, 'a' => float, 'b' => float]
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
        $a = $values['a'] ?? 0;
        $b = $values['b'] ?? 0;

        // Get reference white from illuminant/observer
        $illuminant = $illuminant ?? \Negarity\Color\CIE\CIEIlluminant::D65;
        $observer = $observer ?? \Negarity\Color\CIE\CIEObserver::TwoDegree;
        $refWhite = \Negarity\Color\CIE\CIEIlluminantData::getXYZ($illuminant, $observer);
        $refX = $refWhite['x'];
        $refY = $refWhite['y'];
        $refZ = $refWhite['z'];

        // Lab -> XYZ
        $y = ($l + 16) / 116;
        $x = $a / 500 + $y;
        $z = $y - $b / 200;

        $x3 = pow($x, 3);
        $y3 = pow($y, 3);
        $z3 = pow($z, 3);

        $x = $refX * (($x3 > 0.008856) ? $x3 : (($x - 16/116) / 7.787));
        $y = $refY * (($y3 > 0.008856) ? $y3 : (($y - 16/116) / 7.787));
        $z = $refZ * (($z3 > 0.008856) ? $z3 : (($z - 16/116) / 7.787));

        // Convert XYZ to RGB
        return XYZ::toRGB(['x' => $x, 'y' => $y, 'z' => $z], $illuminant, $observer);
    }

    /**
     * Convert from Lab to XYZ (direct conversion).
     * 
     * @param array<string, float|int> $values Lab values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer
     * @return array<string, float> XYZ values
     */
    public static function toXYZ(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $l = $values['l'] ?? 0;
        $a = $values['a'] ?? 0;
        $b = $values['b'] ?? 0;

        // Get reference white from illuminant/observer
        $illuminant = $illuminant ?? \Negarity\Color\CIE\CIEIlluminant::D65;
        $observer = $observer ?? \Negarity\Color\CIE\CIEObserver::TwoDegree;
        $refWhite = \Negarity\Color\CIE\CIEIlluminantData::getXYZ($illuminant, $observer);
        $refX = $refWhite['x'];
        $refY = $refWhite['y'];
        $refZ = $refWhite['z'];

        // Lab -> XYZ
        $y = ($l + 16) / 116;
        $x = $a / 500 + $y;
        $z = $y - $b / 200;

        $x3 = pow($x, 3);
        $y3 = pow($y, 3);
        $z3 = pow($z, 3);

        $x = $refX * (($x3 > 0.008856) ? $x3 : (($x - 16/116) / 7.787));
        $y = $refY * (($y3 > 0.008856) ? $y3 : (($y - 16/116) / 7.787));
        $z = $refZ * (($z3 > 0.008856) ? $z3 : (($z - 16/116) / 7.787));

        return [
            'x' => round($x, 4),
            'y' => round($y, 4),
            'z' => round($z, 4)
        ];
    }

    /**
     * Convert from Lab to LCh (direct conversion).
     * 
     * @param array<string, float|int> $values Lab values
     * @return array<string, float> LCh values
     */
    public static function toLCh(array $values): array
    {
        $l = $values['l'] ?? 0;
        $a = $values['a'] ?? 0;
        $b = $values['b'] ?? 0;
        
        $c = sqrt($a * $a + $b * $b);
        $h = rad2deg(atan2($b, $a));
        if ($h < 0) {
            $h += 360;
        }
        
        return ['l' => $l, 'c' => $c, 'h' => $h];
    }

    /**
     * Convert from Lab to CMYK (via RGB).
     * 
     * @param array<string, float|int> $values Lab values
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
     * Convert from Lab to HSL (via RGB).
     * 
     * @param array<string, float|int> $values Lab values
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
     * Convert from Lab to HSLA (via RGB and HSL).
     * 
     * @param array<string, float|int> $values Lab values
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
     * Convert from Lab to HSV (via RGB).
     * 
     * @param array<string, float|int> $values Lab values
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
     * Convert from Lab to RGBA (via RGB).
     * 
     * @param array<string, float|int> $values Lab values
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
     * Convert from Lab to YCbCr (via RGB).
     * 
     * @param array<string, float|int> $values Lab values
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
     * Convert from RGB to Lab (via XYZ).
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional illuminant (default: D65)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional observer (default: TwoDegree)
     * @return array<string, float> Lab values: ['l' => float, 'a' => float, 'b' => float]
     */
    public static function fromRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        // First convert RGB to XYZ
        $xyz = \Negarity\Color\ColorSpace\XYZ::fromRGB($values, $illuminant, $observer);
        
        // Then convert XYZ to Lab
        $illuminant = $illuminant ?? \Negarity\Color\CIE\CIEIlluminant::D65;
        $observer = $observer ?? \Negarity\Color\CIE\CIEObserver::TwoDegree;
        $refWhite = \Negarity\Color\CIE\CIEIlluminantData::getXYZ($illuminant, $observer);
        
        // Normalize XYZ using reference white
        $x = ($xyz['x'] / 100) / ($refWhite['x'] / 100);
        $y = ($xyz['y'] / 100) / ($refWhite['y'] / 100);
        $z = ($xyz['z'] / 100) / ($refWhite['z'] / 100);
        
        $delta = 6/29;
        
        $fx = ($x > $delta**3) ? pow($x, 1/3) : ($x/(3*$delta*$delta) + 4/29);
        $fy = ($y > $delta**3) ? pow($y, 1/3) : ($y/(3*$delta*$delta) + 4/29);
        $fz = ($z > $delta**3) ? pow($z, 1/3) : ($z/(3*$delta*$delta) + 4/29);
        
        $l = 116*$fy - 16;
        $a = 500*($fx - $fy);
        $b = 200*($fy - $fz);
        
        return ['l' => $l, 'a' => $a, 'b' => $b];
    }
}

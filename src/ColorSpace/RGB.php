<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;
use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEObserver;
use Negarity\Color\CIE\CIEIlluminantData;

final class RGB extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::RGB->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['r', 'g', 'b'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'r', 'g', 'b' => 0.0,
            default => throw new InvalidColorValueException("Channel '{$name}' does not exist in RGB color space."),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['r', 'g', 'b'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, float $value): void
    {
        match ($channel) {
            'r', 'g', 'b' => static::assertRange($value, 0.0, 255.0, $channel),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in RGB color space."),
        };
    }

    #[\Override]
    public static function clampValue(string $channel, float $value): float
    {
        return match ($channel) {
            'r', 'g', 'b' => static::clampRange($value, 0.0, 255.0),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in RGB color space."),
        };
    }

    /**
     * Convert from CMYK to RGB.
     * 
     * @param array<string, float|int> $values CMYK values: ['c' => int, 'm' => int, 'y' => int, 'k' => int]
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function fromCMYK(array $values): array
    {
        $c = ($values['c'] ?? 0) / 100;
        $m = ($values['m'] ?? 0) / 100;
        $y = ($values['y'] ?? 0) / 100;
        $k = ($values['k'] ?? 0) / 100;
        
        $r = 255 * (1 - $c) * (1 - $k);
        $g = 255 * (1 - $m) * (1 - $k);
        $b = 255 * (1 - $y) * (1 - $k);
        
        return [
            'r' => max(0.0, min(255.0, $r)),
            'g' => max(0.0, min(255.0, $g)),
            'b' => max(0.0, min(255.0, $b))
        ];
    }

    /**
     * Convert from HSL to RGB.
     * 
     * @param array<string, float|int> $values HSL values: ['h' => int, 's' => int, 'l' => int]
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function fromHSL(array $values): array
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

        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));

        return [
            'r' => max(0.0, min(255.0, $r)),
            'g' => max(0.0, min(255.0, $g)),
            'b' => max(0.0, min(255.0, $b))
        ];
    }

    /**
     * Convert from HSLA to RGB (ignores alpha channel).
     * 
     * @param array<string, float|int> $values HSLA values: ['h' => int, 's' => int, 'l' => int, 'a' => int]
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function fromHSLA(array $values): array
    {
        // Use HSL conversion (ignore alpha)
        return static::fromHSL($values);
    }

    /**
     * Convert from HSV to RGB.
     * 
     * @param array<string, float|int> $values HSV values: ['h' => int, 's' => int, 'v' => int]
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function fromHSV(array $values): array
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

        $r = max(0, min(255, ($r + $m) * 255));
        $g = max(0, min(255, ($g + $m) * 255));
        $b = max(0, min(255, ($b + $m) * 255));

        return [
            'r' => max(0.0, min(255.0, $r)),
            'g' => max(0.0, min(255.0, $g)),
            'b' => max(0.0, min(255.0, $b))
        ];
    }

    /**
     * Convert from XYZ to RGB.
     * 
     * @param array<string, float|int> $values XYZ values: ['x' => float, 'y' => float, 'z' => float]
     * @param CIEIlluminant|null $illuminant Optional illuminant (default: D65)
     * @param CIEObserver|null $observer Optional observer (default: TwoDegree)
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function fromXYZ(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
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
     * Convert from Lab to RGB (via XYZ).
     * 
     * @param array<string, float|int> $values Lab values: ['l' => float, 'a' => float, 'b' => float]
     * @param CIEIlluminant|null $illuminant Optional illuminant (default: D65)
     * @param CIEObserver|null $observer Optional observer (default: TwoDegree)
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function fromLab(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        $l = $values['l'] ?? 0;
        $a = $values['a'] ?? 0;
        $b = $values['b'] ?? 0;

        // Get reference white from illuminant/observer
        $illuminant = $illuminant ?? CIEIlluminant::D65;
        $observer = $observer ?? CIEObserver::TwoDegree;
        $refWhite = CIEIlluminantData::getXYZ($illuminant, $observer);
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
        return static::fromXYZ(['x' => $x, 'y' => $y, 'z' => $z], $illuminant, $observer);
    }

    /**
     * Convert from LCh to RGB (via Lab and XYZ).
     * 
     * @param array<string, float|int> $values LCh values: ['l' => float, 'c' => float, 'h' => float]
     * @param CIEIlluminant|null $illuminant Optional illuminant (default: D65)
     * @param CIEObserver|null $observer Optional observer (default: TwoDegree)
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function fromLCh(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        $l = $values['l'] ?? 0;
        $c = $values['c'] ?? 0;
        $h = deg2rad($values['h'] ?? 0);

        // LCh -> Lab
        $a = cos($h) * $c;
        $b = sin($h) * $c;

        // Convert Lab to RGB
        return static::fromLab(['l' => $l, 'a' => $a, 'b' => $b], $illuminant, $observer);
    }

    /**
     * Convert from YCbCr to RGB.
     * 
     * @param array<string, float|int> $values YCbCr values: ['y' => float, 'cb' => int, 'cr' => int]
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function fromYCbCr(array $values): array
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
     * Convert from RGB to RGB (identity conversion).
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (ignored for RGB)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (ignored for RGB)
     * @return array<string, float> RGB values: ['r' => float, 'g' => float, 'b' => float]
     */
    public static function toRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        return [
            'r' => (float) ($values['r'] ?? 0),
            'g' => (float) ($values['g'] ?? 0),
            'b' => (float) ($values['b'] ?? 0)
        ];
    }

    /**
     * Convert from RGB to RGB (identity conversion).
     * 
     * @param array<string, float|int> $values RGB values: ['r' => int, 'g' => int, 'b' => int]
     * @param int $alpha Optional alpha channel (ignored for RGB)
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (ignored for RGB)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (ignored for RGB)
     * @return array<string, float> RGB values: ['r' => float, 'g' => float, 'b' => float]
     */
    public static function fromRGB(
        array $values,
        int $alpha = 255,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        return [
            'r' => (float) ($values['r'] ?? 0),
            'g' => (float) ($values['g'] ?? 0),
            'b' => (float) ($values['b'] ?? 0)
        ];
    }

    /**
     * Convert from RGBA to RGB (ignores alpha channel).
     * 
     * @param array<string, float|int> $values RGBA values: ['r' => int, 'g' => int, 'b' => int, 'a' => int]
     * @return array<string, int> RGB values: ['r' => int, 'g' => int, 'b' => int]
     */
    public static function fromRGBA(array $values): array
    {
        return [
            'r' => (float) ($values['r'] ?? 0),
            'g' => (float) ($values['g'] ?? 0),
            'b' => (float) ($values['b'] ?? 0)
        ];
    }

    /**
     * Convert from RGB to CMYK.
     * 
     * @param array<string, float|int> $values RGB values
     * @return array<string, int> CMYK values
     */
    public static function toCMYK(array $values): array
    {
        return CMYK::fromRGB($values);
    }

    /**
     * Convert from RGB to HSL.
     * 
     * @param array<string, float|int> $values RGB values
     * @return array<string, int> HSL values
     */
    public static function toHSL(array $values): array
    {
        return HSL::fromRGB($values);
    }

    /**
     * Convert from RGB to HSLA.
     * 
     * @param array<string, float|int> $values RGB values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @return array<string, int> HSLA values
     */
    public static function toHSLA(array $values, int $alpha = 255): array
    {
        return HSLA::fromRGB($values, $alpha);
    }

    /**
     * Convert from RGB to HSV.
     * 
     * @param array<string, float|int> $values RGB values
     * @return array<string, int> HSV values
     */
    public static function toHSV(array $values): array
    {
        return HSV::fromRGB($values);
    }

    /**
     * Convert from RGB to RGBA.
     * 
     * @param array<string, float|int> $values RGB values
     * @param int $alpha Alpha channel (0-255, default: 255)
     * @return array<string, int> RGBA values
     */
    public static function toRGBA(array $values, int $alpha = 255): array
    {
        return RGBA::fromRGB($values, $alpha);
    }

    /**
     * Convert from RGB to Lab (via XYZ).
     * 
     * @param array<string, float|int> $values RGB values
     * @param CIEIlluminant|null $illuminant Optional illuminant
     * @param CIEObserver|null $observer Optional observer
     * @return array<string, float> Lab values
     */
    public static function toLab(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        return Lab::fromRGB($values, 255, $illuminant, $observer);
    }

    /**
     * Convert from RGB to LCh (via Lab).
     * 
     * @param array<string, float|int> $values RGB values
     * @param CIEIlluminant|null $illuminant Optional illuminant
     * @param CIEObserver|null $observer Optional observer
     * @return array<string, float> LCh values
     */
    public static function toLCh(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        return LCh::fromRGB($values, 255, $illuminant, $observer);
    }

    /**
     * Convert from RGB to XYZ.
     * 
     * @param array<string, float|int> $values RGB values
     * @param CIEIlluminant|null $illuminant Optional illuminant
     * @param CIEObserver|null $observer Optional observer
     * @return array<string, float> XYZ values
     */
    public static function toXYZ(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        return XYZ::fromRGB($values, 255, $illuminant, $observer);
    }

    /**
     * Convert from RGB to YCbCr.
     * 
     * @param array<string, float|int> $values RGB values
     * @return array<string, float|int> YCbCr values
     */
    public static function toYCbCr(array $values): array
    {
        return YCbCr::fromRGB($values);
    }
}

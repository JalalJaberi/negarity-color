<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEObserver;
use Negarity\Color\Exception\InvalidColorValueException;

/**
 * OKLab perceptual color space (Björn Ottosson, 2020).
 *
 * sRGB colors use the CSS Color 4 path: linear sRGB → LMS → OKLab (not XYZ → OKLab,
 * which would leave a small chromatic residual on neutral sRGB whites).
 */
final class OkLab extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::OKLAB->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['l', 'a', 'b'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'l', 'a', 'b' => 0.0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in OkLab.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['l', 'a', 'b'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, float $value): void
    {
        if (!in_array($channel, ['l', 'a', 'b'], true)) {
            throw new InvalidColorValueException("Channel '{$channel}' does not exist in OkLab.");
        }
        if (!is_numeric($value)) {
            throw new InvalidColorValueException("Channel '{$channel}' must be numeric.");
        }
    }

    #[\Override]
    public static function clampValue(string $channel, float $value): float
    {
        return $value;
    }

    #[\Override]
    public static function supportAlphaChannel(): bool
    {
        return false;
    }

    #[\Override]
    public static function getAlphaChannelName(): string
    {
        return '';
    }

    /**
     * Convert linear sRGB (0–1) to OKLab.
     *
     * @return array{l: float, a: float, b: float}
     */
    private static function fromLinearRgb(float $r, float $g, float $b): array
    {
        $l = 0.4122214708 * $r + 0.5363325363 * $g + 0.0514459929 * $b;
        $m = 0.2119034982 * $r + 0.6806995451 * $g + 0.1073969566 * $b;
        $s = 0.0883024619 * $r + 0.2817188376 * $g + 0.6299787005 * $b;

        $l = self::cbrt($l);
        $m = self::cbrt($m);
        $s = self::cbrt($s);

        return [
            'l' => 0.2104542553 * $l + 0.7936177850 * $m - 0.0040720468 * $s,
            'a' => 1.9779984951 * $l - 2.4285922050 * $m + 0.4505937099 * $s,
            'b' => 0.0259040371 * $l + 0.7827717662 * $m - 0.8086757660 * $s,
        ];
    }

    /**
     * Convert OKLab to linear sRGB (0–1).
     *
     * @param array<string, float|int> $values
     * @return array{r: float, g: float, b: float}
     */
    private static function toLinearRgb(array $values): array
    {
        $l = (float) ($values['l'] ?? 0);
        $a = (float) ($values['a'] ?? 0);
        $b = (float) ($values['b'] ?? 0);

        $l_ = $l + 0.3963377774 * $a + 0.2158037573 * $b;
        $m_ = $l - 0.1055613458 * $a - 0.0638541728 * $b;
        $s_ = $l - 0.0894841775 * $a - 1.2914855480 * $b;

        $l = $l_ * $l_ * $l_;
        $m = $m_ * $m_ * $m_;
        $s = $s_ * $s_ * $s_;

        return [
            'r' => +4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s,
            'g' => -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s,
            'b' => -0.0041960863 * $l - 0.7034196147 * $m + 1.7076147010 * $s,
        ];
    }

    /**
     * @param array<string, float|int> $values
     */
    public static function toRGB(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        $linear = self::toLinearRgb($values);

        return self::linearRgbToSrgb255($linear['r'], $linear['g'], $linear['b']);
    }

    /**
     * @param array<string, float|int> $values
     * @return array{l: float, c: float, h: float}
     */
    public static function toOklch(array $values): array
    {
        return Oklch::fromOkLab($values);
    }

    /**
     * @param array<string, float|int> $values
     */
    public static function fromRGB(
        array $values,
        int $alpha = 255,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        $r = ($values['r'] ?? 0) / 255.0;
        $g = ($values['g'] ?? 0) / 255.0;
        $b = ($values['b'] ?? 0) / 255.0;

        $r = self::srgbToLinear($r);
        $g = self::srgbToLinear($g);
        $b = self::srgbToLinear($b);

        return self::fromLinearRgb($r, $g, $b);
    }

    /**
     * @return array{r: float, g: float, b: float}
     */
    private static function linearRgbToSrgb255(float $r, float $g, float $b): array
    {
        $channels = ['r' => $r, 'g' => $g, 'b' => $b];
        foreach ($channels as $name => $val) {
            if ($val <= 0.0031308) {
                $val = 12.92 * $val;
            } else {
                $val = 1.055 * pow($val, 1.0 / 2.4) - 0.055;
            }
            $channels[$name] = max(0.0, min(255.0, $val * 255.0));
        }

        return $channels;
    }

    private static function srgbToLinear(float $channel): float
    {
        return ($channel > 0.04045)
            ? pow(($channel + 0.055) / 1.055, 2.4)
            : $channel / 12.92;
    }

    private static function cbrt(float $x): float
    {
        return $x >= 0.0 ? pow($x, 1.0 / 3.0) : -pow(-$x, 1.0 / 3.0);
    }
}

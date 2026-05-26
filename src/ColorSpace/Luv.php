<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEIlluminantData;
use Negarity\Color\CIE\CIEObserver;
use Negarity\Color\Exception\InvalidColorValueException;

/**
 * CIE 1976 L*u*v* (CIELUV).
 */
final class Luv extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::LUV->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['l', 'u', 'v'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'l', 'u', 'v' => 0.0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in Luv.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['l', 'u', 'v'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, float $value): void
    {
        match ($channel) {
            'l' => static::assertRange($value, 0.0, 100.0, $channel),
            'u', 'v' => is_numeric($value) ? null : throw new InvalidColorValueException("Channel '{$channel}' must be numeric."),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in Luv."),
        };
    }

    #[\Override]
    public static function clampValue(string $channel, float $value): float
    {
        return match ($channel) {
            'l' => static::clampRange($value, 0.0, 100.0),
            'u', 'v' => $value,
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in Luv."),
        };
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
     * @return array{l: float, u: float, v: float}
     */
    private static function fromXyz100(
        float $x,
        float $y,
        float $z,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        $illuminant = $illuminant ?? CIEIlluminant::D65;
        $observer = $observer ?? CIEObserver::TwoDegree;
        $ref = CIEIlluminantData::getXYZ($illuminant, $observer);
        $refX = $ref['x'];
        $refY = $ref['y'];
        $refZ = $ref['z'];

        $den = $x + 15.0 * $y + 3.0 * $z;
        $refDen = $refX + 15.0 * $refY + 3.0 * $refZ;

        if (abs($den) < 1e-9 || abs($refDen) < 1e-9) {
            return ['l' => 0.0, 'u' => 0.0, 'v' => 0.0];
        }

        $u = 4.0 * $x / $den;
        $v = 9.0 * $y / $den;
        $un = 4.0 * $refX / $refDen;
        $vn = 9.0 * $refY / $refDen;

        $yr = $y / $refY;
        $l = $yr > 0.008856
            ? 116.0 * self::cbrt($yr) - 16.0
            : 903.3 * $yr;

        return [
            'l' => $l,
            'u' => 13.0 * $l * ($u - $un),
            'v' => 13.0 * $l * ($v - $vn),
        ];
    }

    /**
     * @param array<string, float|int> $values
     * @return array{x: float, y: float, z: float}
     */
    private static function toXyz100(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        $illuminant = $illuminant ?? CIEIlluminant::D65;
        $observer = $observer ?? CIEObserver::TwoDegree;
        $ref = CIEIlluminantData::getXYZ($illuminant, $observer);
        $refX = $ref['x'];
        $refY = $ref['y'];
        $refZ = $ref['z'];

        $l = (float) ($values['l'] ?? 0);
        $u = (float) ($values['u'] ?? 0);
        $v = (float) ($values['v'] ?? 0);

        $refDen = $refX + 15.0 * $refY + 3.0 * $refZ;
        $un = 4.0 * $refX / $refDen;
        $vn = 9.0 * $refY / $refDen;

        $y = $l > 8.0
            ? $refY * pow(($l + 16.0) / 116.0, 3)
            : $refY * ($l / 903.3);

        if (abs($l) < 1e-9) {
            return ['x' => 0.0, 'y' => 0.0, 'z' => 0.0];
        }

        $uPrime = $u / (13.0 * $l) + $un;
        $vPrime = $v / (13.0 * $l) + $vn;

        $x = $y * ((9.0 * $uPrime) / (4.0 * $vPrime));
        $z = $y * ((12.0 - 3.0 * $uPrime - 20.0 * $vPrime) / (4.0 * $vPrime));

        return ['x' => $x, 'y' => $y, 'z' => $z];
    }

    /**
     * @param array<string, float|int> $values
     */
    public static function toRGB(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        return XYZ::toRGB(self::toXyz100($values, $illuminant, $observer), $illuminant, $observer);
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
        $xyz = XYZ::fromRGB($values, $alpha, $illuminant, $observer);

        return self::fromXyz100($xyz['x'], $xyz['y'], $xyz['z'], $illuminant, $observer);
    }

    private static function cbrt(float $x): float
    {
        return $x >= 0.0 ? pow($x, 1.0 / 3.0) : -pow(-$x, 1.0 / 3.0);
    }
}

<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEObserver;
use Negarity\Color\Exception\InvalidColorValueException;

/**
 * OKLCH — cylindrical form of OKLab (L, C, h).
 */
final class Oklch extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::OKLCH->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['l', 'c', 'h'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'l', 'c', 'h' => 0.0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in Oklch.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['l', 'c', 'h'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, float $value): void
    {
        match ($channel) {
            'l' => static::assertRange($value, 0.0, 1.0, $channel),
            'c' => is_numeric($value) ? null : throw new InvalidColorValueException("Channel '{$channel}' must be numeric."),
            'h' => static::assertRange($value, 0.0, 360.0, $channel),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in Oklch."),
        };
    }

    #[\Override]
    public static function clampValue(string $channel, float $value): float
    {
        return match ($channel) {
            'l' => static::clampRange($value, 0.0, 1.0),
            'h' => static::clampRange($value, 0.0, 360.0),
            'c' => $value,
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in Oklch."),
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

    /**
     * @param array<string, float|int> $values OKLab l,a,b
     * @return array{l: float, c: float, h: float}
     */
    public static function fromOkLab(array $values): array
    {
        $l = (float) ($values['l'] ?? 0);
        $a = (float) ($values['a'] ?? 0);
        $b = (float) ($values['b'] ?? 0);

        $c = sqrt($a * $a + $b * $b);
        $h = rad2deg(atan2($b, $a));
        if ($h < 0.0) {
            $h += 360.0;
        }

        return ['l' => $l, 'c' => $c, 'h' => $h];
    }

    /**
     * @param array<string, float|int> $values Oklch l,c,h
     * @return array{l: float, a: float, b: float}
     */
    public static function toOkLab(array $values): array
    {
        $l = (float) ($values['l'] ?? 0);
        $c = (float) ($values['c'] ?? 0);
        $h = deg2rad((float) ($values['h'] ?? 0));

        return [
            'l' => $l,
            'a' => $c * cos($h),
            'b' => $c * sin($h),
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
        return OkLab::toRGB(self::toOkLab($values), $illuminant, $observer);
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
        return self::fromOkLab(OkLab::fromRGB($values, $alpha, $illuminant, $observer));
    }
}

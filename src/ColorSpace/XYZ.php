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

    public static function getChannelDefaultValue(string $name): float|int
    {
        return match ($name) {
            'x', 'y', 'z' => 0,
            default => throw new InvalidColorValueException("Channel '{$name}' does not exist in color space 'xyz'."),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['x', 'y', 'z'], true);
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'x', 'y', 'z' => static::assertRange((float)$value, 0.0, 100.0, $channel),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in color space 'xyz'."),
        };
    }
}

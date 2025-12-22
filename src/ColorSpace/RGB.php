<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class RGB extends AbstractColorSpace
{
    public static function getName(): string
    {
        return ColorSpaceEnum::RGB->value;
    }

    public static function getChannels(): array
    {
        return ['r', 'g', 'b'];
    }

    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'r', 'g', 'b' => 0,
            default => throw new InvalidColorValueException("Channel '{$name}' does not exist in RGB color space."),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['r', 'g', 'b'], true);
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'r', 'g', 'b' => static::assertRange((int)$value, 0, 255, $channel),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in RGB color space."),
        };
    }
}

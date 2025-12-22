<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class LCh extends AbstractColorSpace
{
    public static function getName(): string
    {
        return ColorSpaceEnum::LCH->value;
    }

    public static function getChannels(): array
    {
        return ['l', 'c', 'h'];
    }

    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'l', 'c', 'h' => 0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in Lab color space.', $name)),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['l', 'c', 'h'], true);
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'l' => static::assertRange((int)$value, 0, 100, $channel),
            'c' => static::assertRange((int)$value, 0, 127, $channel),
            'h' => static::assertRange((int)$value, 0, 360, $channel),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in color space '{static::getName()}'."),
        };
    }
}

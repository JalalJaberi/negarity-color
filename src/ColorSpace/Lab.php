<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class Lab extends AbstractColorSpace
{
    public static function getName(): string
    {
        return ColorSpaceEnum::LAB->value;
    }

    public static function getChannels(): array
    {
        return ['l', 'a', 'b'];
    }

    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'l', 'a', 'b' => 0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in Lab color space.', $name)),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['l', 'a', 'b'], true);
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'l' => static::assertRange((int)$value, 0, 100, $channel),
            'a', 'b' => static::assertRange((int)$value, -128, 127, $channel),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in Lab color space.', $channel)),
        };
    }
}

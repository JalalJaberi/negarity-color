<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class HSL extends AbstractColorSpace
{
    public static function getName(): string
    {
        return ColorSpaceEnum::HSL->value;
    }

    public static function getChannels(): array
    {
        return ['h', 's', 'l'];
    }

    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'h', 's', 'l' => 0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSL color space.', $name)),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['h', 's', 'l'], true);
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'h' => static::assertRange((int)$value, 0, 360, $channel),
            's', 'l' => static::assertRange((int)$value, 0, 100, $channel),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSL color space.', $channel)),
        };
    }
}

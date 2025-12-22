<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class HSV extends AbstractColorSpace
{
    public static function getName(): string
    {
        return ColorSpaceEnum::HSV->value;
    }

    public static function getChannels(): array
    {
        return ['h', 's', 'v'];
    }

    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'h', 's', 'v' => 0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSV color space.', $name)),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['h', 's', 'v'], true);
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'h' => static::assertRange((int)$value, 0, 360, $channel),
            's', 'v' => static::assertRange((int)$value, 0, 100, $channel),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSV color space.', $channel)),
        };
    }
}

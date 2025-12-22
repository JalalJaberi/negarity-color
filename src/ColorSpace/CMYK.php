<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class CMYK extends AbstractColorSpace
{
    public static function getName(): string
    {
        return ColorSpaceEnum::CMYK->value;
    }

    public static function getChannels(): array
    {
        return ['c', 'm', 'y', 'k'];
    }

    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'c', 'm', 'y', 'k' => 0,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in CMYK color space.', $name)),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['c', 'm', 'y', 'k'], true);
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'c', 'm', 'y', 'k' => static::assertRange((int)$value, 0, 100, $channel),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in CMYK color space.', $channel)),
        };
    }
}

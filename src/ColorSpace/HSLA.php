<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class HSLA extends AbstractColorSpace
{
    #[\Override]
    public static function getName(): string
    {
        return ColorSpaceEnum::HSLA->value;
    }

    #[\Override]
    public static function getChannels(): array
    {
        return ['h', 's', 'l', 'a'];
    }

    #[\Override]
    public static function getChannelDefaultValue(string $name): int
    {
        return match ($name) {
            'h', 's', 'l' => 0,
            'a' => 255,
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSLA color space.', $name)),
        };
    }

    #[\Override]
    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['h', 's', 'l', 'a'], true);
    }

    #[\Override]
    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'h' => static::assertRange((int)$value, 0, 360, $channel),
            's', 'l' => static::assertRange((int)$value, 0, 100, $channel),
            'a' => static::assertRange((int)$value, 0, 255, $channel),
            default => throw new InvalidColorValueException(sprintf('Channel "%s" does not exist in HSLA color space.', $channel)),
        };
    }
}

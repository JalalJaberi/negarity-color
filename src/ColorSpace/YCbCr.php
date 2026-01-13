<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class YCbCr extends AbstractColorSpace
{
    public static function getName(): string
    {
        return ColorSpaceEnum::YCBCR->value;
    }

    public static function getChannels(): array
    {
        return ['y', 'cb', 'cr'];
    }

    public static function getChannelDefaultValue(string $name): float|int
    {
        return match ($name) {
            'y', 'cb', 'cr' => 0,
            default => throw new InvalidColorValueException("Channel '{$name}' does not exist in color space 'ycbcr'."),
        };
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['y', 'cb', 'cr'], true);
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'y' => static::assertRange((float)$value, 0.0, 100.0, $channel),
            'cb', 'cr' => static::assertRange((int)$value, -128, 127, $channel),
            default => throw new InvalidColorValueException("Channel '{$channel}' does not exist in color space 'ycbcr'."),
        };
    }
}

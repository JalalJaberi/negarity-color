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
        if (!is_numeric($value)) {
            throw new InvalidColorValueException("Channel '{$channel}' must be numeric.");
        }
    }

    #[\Override]
    public static function supportsIlluminant(): bool
    {
        return true;
    }

    #[\Override]
    public static function supportsObserver(): bool
    {
        return true;
    }
}

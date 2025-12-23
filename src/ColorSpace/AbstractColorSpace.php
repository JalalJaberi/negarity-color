<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;

abstract class AbstractColorSpace implements ColorSpaceInterface
{
    abstract public static function getName(): string;
    abstract public static function getChannels(): array;
    abstract public static function hasChannel(string $name): bool;
    abstract public static function getChannelDefaultValue(string $name): float|int;
    abstract public static function validateValue(string $channel, int|float $value): void;

    /**
     * Asserts that a given value is within the specified range.
     * 
     * @param int $value The value to check.
     * @param int $min The minimum allowed value.
     * @param int $max The maximum allowed value.
     * @param string $channel The name of the channel for error messaging.
     * @return void
     * @throws InvalidColorValueException if the value is out of range.
     */
    final protected static function assertRange(int $value, int $min, int $max, string $channel): void
    {
        if ($value < $min || $value > $max) {
            throw new InvalidColorValueException(
                sprintf('Channel "%s" must be between %d and %d, got %d', $channel, $min, $max, $value)
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

interface ColorSpaceInterface
{
    /**
     * Returns the unique name of the color space (e.g., "rgb", "hsl", "lab").
     */
    public static function getName(): string;

    /**
     * Returns all channel names in order (e.g., ["r", "g", "b"]).
     */
    public static function getChannels(): array;

    /**
     * Checks if the channel exists by name.
     *
     * @return bool
     */
    public static function hasChannel(string $name): bool;

    /**
     * Returns the default channel value by name (normalized float or int).
     *
     * @throws \Negarity\Color\Exception\InvalidColorValueException
     */
    public static function getChannelDefaultValue(string $name): float|int;

    /**
     * Validates a channel value for a given channel name.
     *
     * @throws \Negarity\Color\Exception\InvalidColorValueException
     */
    public static function validateValue(string $channel, int|float $value): void;
}

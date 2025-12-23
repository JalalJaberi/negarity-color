<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

interface ColorSpaceInterface
{
    /**
     * Returns the unique name of the color space (e.g., "rgb", "hsl", "lab").
     * 
     * @return string
     */
    public static function getName(): string;

    /**
     * Returns all channel names in order (e.g., ["r", "g", "b"]).
     * 
     * @return string[]
     */
    public static function getChannels(): array;

    /**
     * Checks if the channel exists by name.
     * 
     * @param string $name The name of the channel.
     * @return bool
     */
    public static function hasChannel(string $name): bool;

    /**
     * Returns the default channel value by name (normalized float or int).
     * 
     * @param string $name The name of the channel.
     * @return float|int
     * @throws \Negarity\Color\Exception\InvalidColorValueException
     */
    public static function getChannelDefaultValue(string $name): float|int;

    /**
     * Validates a channel value for a given channel name.
     * 
     * @param string $channel The name of the channel.
     * @param int|float $value The value to validate.
     * @return void
     * @throws \Negarity\Color\Exception\InvalidColorValueException
     */
    public static function validateValue(string $channel, int|float $value): void;
}

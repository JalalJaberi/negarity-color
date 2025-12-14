<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

interface ColorSpaceInterface
{
    /**
     * Returns the unique name of the color space (e.g., "rgb", "hsl", "lab").
     */
    public function getName(): string;

    /**
     * Returns all channel names in order (e.g., ["r", "g", "b"]).
     */
    public function getChannels(): array;

    /**
     * Returns the channel value by name (normalized float or int).
     *
     * @throws \Negarity\Color\Exception\InvalidColorValueException
     */
    public function getChannel(string $name): float|int;

    // @TODO: enable once mutability is supported
    /**
     * Set the channel value by name (normalized float or int).
     *
     * @throws \Negarity\Color\Exception\InvalidColorValueException
     */
    // public function setChannel(string $name, float|int $value);

    /**
     * Returns an associative array of all channels and their values.
     *
     * Example: ["r" => 255, "g" => 100, "b" => 10]
     */
    public function toArray(): array;

    /**
     * Returns a clone with modified channels (immutably).
     *
     * Example: $color->with(['r' => 200])
     */
    public function with(array $channels): static;

    /**
     * Returns a clone without specified channels (immutably).
     *
     * Example: $color->without(['a'])
     */
    public function without(array $channels): static;

    /**
     * Returns a string representation (e.g., hex or "rgb(255, 100, 10)").
     */
    public function __toString(): string;
}

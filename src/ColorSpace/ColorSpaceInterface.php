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
     * Returns the default channel value by name.
     * 
     * @param string $name The name of the channel.
     * @return float
     * @throws \Negarity\Color\Exception\InvalidColorValueException
     */
    public static function getChannelDefaultValue(string $name): float;

    /**
     * Validates a channel value for a given channel name.
     * 
     * @param string $channel The name of the channel.
     * @param float $value The value to validate.
     * @return void
     * @throws \Negarity\Color\Exception\InvalidColorValueException
     */
    public static function validateValue(string $channel, float $value): void;

    /**
     * Clamps a channel value to its valid range.
     * 
     * @param string $channel The name of the channel.
     * @param float $value The value to clamp.
     * @return float The clamped value within the valid range.
     */
    public static function clampValue(string $channel, float $value): float;

    /**
     * Convert from this color space to RGB.
     * 
     * @param array<string, float> $values Color space values
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (for color spaces that support it)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (for color spaces that support it)
     * @return array<string, float> RGB values: ['r' => float, 'g' => float, 'b' => float]
     */
    public static function toRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array;

    /**
     * Convert from RGB to this color space.
     * 
     * @param array<string, float> $values RGB values: ['r' => float, 'g' => float, 'b' => float]
     * @param int $alpha Optional alpha channel (for color spaces that support it, e.g., RGBA, HSLA)
     * @param \Negarity\Color\CIE\CIEIlluminant|null $illuminant Optional CIE illuminant (for color spaces that support it)
     * @param \Negarity\Color\CIE\CIEObserver|null $observer Optional CIE observer (for color spaces that support it)
     * @return array<string, float> Color space values
     */
    public static function fromRGB(
        array $values,
        int $alpha = 255,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array;
}

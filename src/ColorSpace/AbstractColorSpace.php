<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;

abstract class AbstractColorSpace implements ColorSpaceInterface
{
    abstract public static function getName(): string;
    abstract public static function getChannels(): array;
    abstract public static function hasChannel(string $name): bool;
    abstract public static function getChannelDefaultValue(string $name): float;
    abstract public static function validateValue(string $channel, float $value): void;
    abstract public static function toRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array;
    abstract public static function fromRGB(
        array $values,
        int $alpha = 255,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array;

    abstract public static function supportAlphaChannel(): bool;
    abstract public static function getAlphaChannelName(): string;

    /**
     * Check if this color space supports CIE standard illuminants.
     * 
     * @return bool True if the color space uses illuminants (XYZ, Lab, LCh), false otherwise
     */
    public static function supportsIlluminant(): bool
    {
        return false;
    }

    /**
     * Check if this color space supports CIE standard observers.
     * 
     * @return bool True if the color space uses observers (XYZ, Lab, LCh), false otherwise
     */
    public static function supportsObserver(): bool
    {
        return false;
    }

    /**
     * Asserts that a given value is within the specified range.
     * 
     * @param float $value The value to check.
     * @param float $min The minimum allowed value.
     * @param float $max The maximum allowed value.
     * @param string $channel The name of the channel for error messaging.
     * @return void
     * @throws InvalidColorValueException if the value is out of range.
     */
    final protected static function assertRange(float $value, float $min, float $max, string $channel): void
    {
        if ($value < $min || $value > $max) {
            throw new InvalidColorValueException(
                sprintf('Channel "%s" must be between %.2f and %.2f, got %.2f', 
                    $channel, $min, $max, $value)
            );
        }
    }

    /**
     * Clamps a value to the specified range.
     * 
     * @param float $value The value to clamp.
     * @param float $min The minimum allowed value.
     * @param float $max The maximum allowed value.
     * @return float The clamped value.
     */
    final protected static function clampRange(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}

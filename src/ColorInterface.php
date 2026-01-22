<?php

declare(strict_types=1);

namespace Negarity\Color;

use Negarity\Color\ColorSpace\ColorSpaceInterface;

interface ColorInterface
{
    /**
     * Get the color space class name.
     * 
     * @return class-string<ColorSpaceInterface>
     */
    public function getColorSpace(): string;
    /**
     * Get the color space name.
     * 
     * @return string
     */
    public function getColorSpaceName(): string;
    /**
     * Get all color channels as an associative array.
     * 
     * @return array<string, float>
     */
    public function getChannels(): array;
    /**
     * Get a specific color channel by name.
     * 
     * @param string $name
     * @return float
     */
    public function getChannel(string $name): float;
    /**
     * Get all color channels as a numeric array.
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array;
    /**
     * Create a new color instance without the specified channels.
     * 
     * @param array<string> $channels
     * @return static
     */
    public function without(array $channels): static;
    /**
     * Create a new color instance with the specified channels.
     * 
     * @param array<string, float|int> $channels Accepts int|float for convenience, stores as float
     * @return static
     */
    public function with(array $channels): static;
    /**
     * Get the string representation of the color.
     * 
     * @return string
     */
    public function __toString(): string;
}
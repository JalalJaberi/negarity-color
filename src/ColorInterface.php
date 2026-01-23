<?php

declare(strict_types=1);

namespace Negarity\Color;

use Negarity\Color\ColorSpace\ColorSpaceInterface;
use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEObserver;
use Negarity\Color\CIE\AdaptationMethod;

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

    /**
     * Create a new color instance with a different illuminant (metadata only, no conversion).
     * 
     * @param CIEIlluminant $illuminant The new illuminant
     * @return static
     * @throws \RuntimeException If the color space does not support illuminants
     */
    public function withIlluminant(CIEIlluminant $illuminant): static;

    /**
     * Adapt the color to a different illuminant using chromatic adaptation.
     * 
     * This method performs chromatic adaptation to make the color appear the same
     * under a different illuminant. The color values are converted accordingly.
     * 
     * @param CIEIlluminant $targetIlluminant The target illuminant
     * @param AdaptationMethod|null $method The adaptation method (default: Bradford)
     * @return static
     * @throws \RuntimeException If the color space does not support illuminants
     */
    public function adaptIlluminant(
        CIEIlluminant $targetIlluminant,
        ?AdaptationMethod $method = null
    ): static;

    /**
     * Adapt the color to a different observer.
     * 
     * This method converts the color values to account for the different observer's
     * color matching functions (2° vs 10°).
     * 
     * @param CIEObserver $targetObserver The target observer
     * @return static
     * @throws \RuntimeException If the color space does not support observers
     */
    public function adaptObserver(CIEObserver $targetObserver): static;
}
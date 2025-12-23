<?php

declare(strict_types=1);

namespace Negarity\Color\Registry;

use Negarity\Color\ColorSpace\ColorSpaceInterface;

interface NamedColorRegistryInterface
{
    /**
     * Check if a named color exists in the registry for the given color space.
     * 
     * @param string $colorName
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @return bool
     */
    public function has(string $colorName, string $colorSpace): bool;

    /**
     * Get the color channel values for a named color in the specified color space.
     * 
     * @param string $colorName
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @return array<string, float|int>
     */
    public function getColorValuesByName(string $colorName, string $colorSpace): array;
}

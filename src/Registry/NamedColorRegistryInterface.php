<?php

declare(strict_types=1);

namespace Negarity\Color\Registry;

use Negarity\Color\ColorSpace\ColorSpaceInterface;

interface NamedColorRegistryInterface
{
    /**
     * @param string $colorName
     * @param class-string<ColorSpaceInterface> $colorSpace
     * 
     * @return bool
     */
    public function has(string $colorName, string $colorSpace): bool;
    /**
     * @param string $colorName
     * @param class-string<ColorSpaceInterface> $colorSpace
     * 
     * @return array<string, float|int>
     */
    public function getColorValuesByName(string $colorName, string $colorSpace): array;
}

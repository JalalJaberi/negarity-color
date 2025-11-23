<?php

declare(strict_types=1);

namespace Negarity\Color\Registry;

use Negarity\Color\ColorSpace\ColorSpaceInterface;

interface NamedColorRegistryInterface
{
    public function has(string $colorName, string $colorSpaceName): bool;
    public function getColorByName(string $colorName, string $colorSpaceName): ColorSpaceInterface;
}

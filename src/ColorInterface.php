<?php

declare(strict_types=1);

namespace Negarity\Color;

use Negarity\Color\ColorSpace\ColorSpaceInterface;

interface ColorInterface
{
    /** @return class-string<ColorSpaceInterface> */
    public function getColorSpace(): string;
    public function getColorSpaceName(): string;
    public function getChannels(): array;
    public function getChannel(string $name): float|int;
    public function toArray(): array;
    public function without(array $channels): static;
    public function with(array $channels): static;
    public function __toString(): string;
}
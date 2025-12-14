<?php

namespace Negarity\Color;

use Negarity\Color\ColorSpace\ColorSpaceInterface;

interface ColorInterface
{
    public function getColorSpace(): ColorSpaceInterface;
    public function getColorSpaceName(): string;
    public function getChannels(): array;
    public function getChannel(string $name): float|int;
    public function toArray(): array;
    public function without(array $channels): static;
    public function with(array $channels): static;
}
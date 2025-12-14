<?php

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\Color;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

class BlendFilter implements BinaryColorFilterInterface
{
    public function getName(): string { return 'blend'; }

    public function apply(Color $base, Color $blend): Color
    {
        if ($base->getColorSpace()->getName() != $blend->getColorSpace()->getName()) {
            throw new \InvalidArgumentException('Colors must be in the same color space to blend.');
        }

        $baseChannels = $base->toArray();
        $blendChannels = $blend->toArray();
        $resultChannels = [];

        foreach ($baseChannels as $channel => $value) {
            $blendValue = $blendChannels[$channel] ?? 0;
            $resultChannels[$channel] = (int)((1 - 0.5) * $value + 0.5 * $blendValue);
        }

        return new Color($base->getColorSpace(), $resultChannels);
    }
}
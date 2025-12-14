<?php

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\Color;

class MixFilter implements BinaryColorFilterInterface
{
    private float $weight;

    public function __construct(float $weight = 0.5) { $this->weight = max(0, min(1, $weight)); }

    public function getName(): string { return 'mix'; }

    public function apply(Color $base, Color $blend): Color
    {
        $baseChannels = $base->toArray();
        $blendChannels = $blend->toArray();
        $resultChannels = [];

        foreach ($baseChannels as $channel => $value) {
            $blendValue = $blendChannels[$channel] ?? 0;
            $resultChannels[$channel] = (int)((1 - $this->weight) * $value + $this->weight * $blendValue);
        }

        return new Color($base->getColorSpace(), $resultChannels);
    }
}
<?php

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\ColorInterface;

class MixFilter implements BinaryColorFilterInterface
{
    private float $weight;

    public function __construct(float $weight = 0.5) { $this->weight = max(0, min(1, $weight)); }

    public function getName(): string { return 'mix'; }

    public function apply(ColorInterface $base, ColorInterface $blend): ColorInterface
    {
        if ($base->getColorSpace() != $blend->getColorSpace()) {
            throw new \InvalidArgumentException('Colors must be in the same color space to blend.');
        }

        $baseChannels = $base->toArray()['values'];
        $blendChannels = $blend->toArray()['values'];
        $resultChannels = [];

        foreach ($baseChannels as $channel => $value) {
            $blendValue = $blendChannels[$channel] ?? 0;
            $resultChannels[$channel] = (int)((1 - $this->weight) * $value + $this->weight * $blendValue);
        }

        return $base->with($resultChannels);
    }
}
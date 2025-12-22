<?php

declare(strict_types=1);

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

class BlendFilter implements BinaryColorFilterInterface
{
    public function getName(): string { return 'blend'; }

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
            $resultChannels[$channel] = (int)((1 - 0.5) * $value + 0.5 * $blendValue);
        }

        return $base->with($resultChannels);
    }
}
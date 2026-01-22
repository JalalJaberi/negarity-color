<?php

declare(strict_types=1);

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\ColorInterface;

final class BlendFilter implements BinaryColorFilterInterface
{
    #[\Override]
    public function getName(): string
    {
        return 'blend';
    }

    #[\Override]
    public function apply(ColorInterface $base, ColorInterface $blend): ColorInterface
    {
        // If colors are in the same color space, use direct blending
        if ($base->getColorSpace() === $blend->getColorSpace()) {
            $baseChannels = $base->getChannels();
            $resultChannels = [];

            foreach ($baseChannels as $channel) {
                $baseValue = $base->getChannelForCalculation($channel);
                $blendValue = $blend->getChannelForCalculation($channel);
                $resultChannels[$channel] = (1 - 0.5) * $baseValue + 0.5 * $blendValue;
            }

            return $base->with($resultChannels);
        }

        // Fallback: Convert both to RGB, blend, then convert back to base color space
        $baseRgb = $base->toRGB();
        $blendRgb = $blend->toRGB();
        
        $resultRgb = [
            'r' => (1 - 0.5) * $baseRgb->getChannelForCalculation('r') + 0.5 * $blendRgb->getChannelForCalculation('r'),
            'g' => (1 - 0.5) * $baseRgb->getChannelForCalculation('g') + 0.5 * $blendRgb->getChannelForCalculation('g'),
            'b' => (1 - 0.5) * $baseRgb->getChannelForCalculation('b') + 0.5 * $blendRgb->getChannelForCalculation('b'),
        ];
        
        // Convert back to base color space
        $originalColorSpace = $base->getColorSpace();
        $baseArray = $base->toArray();
        
        // Get alpha if present
        $alpha = 255;
        if (isset($baseArray['values']['a'])) {
            $alpha = (int)$baseArray['values']['a'];
        }
        
        // Get CIE parameters if available
        $illuminant = null;
        $observer = null;
        if (method_exists($base, 'getIlluminant')) {
            $illuminant = $base->getIlluminant();
        }
        if (method_exists($base, 'getObserver')) {
            $observer = $base->getObserver();
        }
        
        $originalValues = $originalColorSpace::fromRGB(
            $resultRgb,
            $alpha,
            $illuminant,
            $observer
        );
        
        return $base->with($originalValues);
    }
}
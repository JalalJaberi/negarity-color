<?php

declare(strict_types=1);

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\ColorInterface;

final class MixFilter implements BinaryColorFilterInterface
{
    private float $weight;

    /**
     * Construct a MixFilter with the given weight.
     * 
     * @param float $weight The weight of the blend color in the mix (0.0 to 1.0).
     */
    public function __construct(float $weight = 0.5)
    {
        $this->weight = max(0, min(1, $weight));
    }

    #[\Override]
    public function getName(): string
    {
        return 'mix';
    }

    #[\Override]
    public function apply(ColorInterface $base, ColorInterface $blend): ColorInterface
    {
        // If colors are in the same color space, use direct mixing
        if ($base->getColorSpace() === $blend->getColorSpace()) {
            $baseChannels = $base->getChannels();
            $resultChannels = [];

            foreach ($baseChannels as $channel) {
                $baseValue = $base->getChannelForCalculation($channel);
                $blendValue = $blend->getChannelForCalculation($channel);
                $resultChannels[$channel] = (1 - $this->weight) * $baseValue + $this->weight * $blendValue;
            }

            return $base->with($resultChannels);
        }

        // Fallback: Convert both to RGB, mix, then convert back to base color space
        $baseRgb = $base->toRGB();
        $blendRgb = $blend->toRGB();
        
        $resultRgb = [
            'r' => (1 - $this->weight) * $baseRgb->getChannelForCalculation('r') + $this->weight * $blendRgb->getChannelForCalculation('r'),
            'g' => (1 - $this->weight) * $baseRgb->getChannelForCalculation('g') + $this->weight * $blendRgb->getChannelForCalculation('g'),
            'b' => (1 - $this->weight) * $baseRgb->getChannelForCalculation('b') + $this->weight * $blendRgb->getChannelForCalculation('b'),
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
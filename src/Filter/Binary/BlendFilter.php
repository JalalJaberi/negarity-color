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
            $baseChannels = $base->toArray()['values'];
            $blendChannels = $blend->toArray()['values'];
            $resultChannels = [];

            foreach ($baseChannels as $channel => $value) {
                $blendValue = $blendChannels[$channel] ?? 0;
                $resultChannels[$channel] = (int)((1 - 0.5) * $value + 0.5 * $blendValue);
            }

            return $base->with($resultChannels);
        }

        // Fallback: Convert both to RGB, blend, then convert back to base color space
        $baseRgb = $base->toRGB();
        $blendRgb = $blend->toRGB();
        
        $baseRgbValues = $baseRgb->toArray()['values'];
        $blendRgbValues = $blendRgb->toArray()['values'];
        
        $resultRgb = [
            'r' => (int)((1 - 0.5) * $baseRgbValues['r'] + 0.5 * $blendRgbValues['r']),
            'g' => (int)((1 - 0.5) * $baseRgbValues['g'] + 0.5 * $blendRgbValues['g']),
            'b' => (int)((1 - 0.5) * $baseRgbValues['b'] + 0.5 * $blendRgbValues['b']),
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
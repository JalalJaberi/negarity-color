<?php

declare(strict_types=1);

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\FilterInterface;

interface BinaryColorFilterInterface extends FilterInterface
{
    /**
     * Apply the binary color filter to the given base and blend colors.
     * 
     * @param ColorInterface $base The base color.
     * @param ColorInterface $blend The blend color.
     * @return ColorInterface The resulting color after applying the filter.
     * @throws \InvalidArgumentException If the colors are not compatible for the filter operation.
     */
    public function apply(ColorInterface $base, ColorInterface $blend): ColorInterface;
}
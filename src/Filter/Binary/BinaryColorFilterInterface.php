<?php

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\FilterInterface;

interface BinaryColorFilterInterface extends FilterInterface
{
    public function apply(ColorInterface $base, ColorInterface $blend): ColorInterface;
}
<?php

namespace Negarity\Color\Filter\Binary;

use Negarity\Color\Color;
use Negarity\Color\Filter\FilterInterface;

interface BinaryColorFilterInterface extends FilterInterface
{
    public function apply(Color $base, Color $blend): Color;
}
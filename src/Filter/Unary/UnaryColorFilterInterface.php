<?php

namespace Negarity\Color\Filter\Unary;

use Negarity\Color\Color;
use Negarity\Color\Filter\FilterInterface;

interface UnaryColorFilterInterface extends FilterInterface
{
    public function apply(Color $color): Color;
}
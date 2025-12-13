<?php

namespace Negarity\Color\Filter\Parameterized;

use Negarity\Color\Color;
use Negarity\Color\Filter\FilterInterface;

interface ParameterizedColorFilterInterface extends FilterInterface
{
    public function apply(Color $color, mixed $value): Color;
}
<?php

declare(strict_types=1);

namespace Negarity\Color\Filter\Parameterized;

use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\FilterInterface;

interface ParameterizedColorFilterInterface extends FilterInterface
{
    public function apply(ColorInterface $color, mixed $value): ColorInterface;
}
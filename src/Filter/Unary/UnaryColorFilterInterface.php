<?php

declare(strict_types=1);

namespace Negarity\Color\Filter\Unary;

use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\FilterInterface;

interface UnaryColorFilterInterface extends FilterInterface
{
    public function apply(ColorInterface $color): ColorInterface;
}
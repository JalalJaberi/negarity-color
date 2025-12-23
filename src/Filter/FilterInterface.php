<?php

declare(strict_types=1);

namespace Negarity\Color\Filter;

interface FilterInterface
{
    /**
     * Get the name of the filter.
     * 
     * @return string
     */
    public function getName(): string;
}
<?php

declare(strict_types=1);

namespace Negarity\Color\Filter;

use Negarity\Color\Exception\FilterNotFoundException;

class FilterRegistry
{
    private static array $filters = [];

    public static function register(FilterInterface $filter): void
    {
        self::$filters[$filter->getName()] = $filter;
    }

    public static function get(string $name): FilterInterface
    {
        if (!isset(self::$filters[$name])) {
            throw new FilterNotFoundException("Filter '{$name}' not registered.");
        }
        return self::$filters[$name];
    }

    public static function has(string $name): bool
    {
        return isset(self::$filters[$name]);
    }
}

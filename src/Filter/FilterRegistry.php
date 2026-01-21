<?php

declare(strict_types=1);

namespace Negarity\Color\Filter;

use Negarity\Color\Exception\FilterNotFoundException;

final class FilterRegistry
{
    /**
     * @var array<string, FilterInterface>
    */
    private static array $filters = [];

    /**
     * Register a filter.
     * 
     * @param FilterInterface $filter The filter to register.
     * @return void
     */
    public static function register(FilterInterface $filter): void
    {
        self::$filters[$filter->getName()] = $filter;
    }

    /**
     * Get a filter by name.
     * 
     * @param string $name The name of the filter.
     * @return FilterInterface The filter instance.
     * @throws FilterNotFoundException If the filter is not registered.
     */
    public static function get(string $name): FilterInterface
    {
        if (!isset(self::$filters[$name])) {
            throw new FilterNotFoundException("Filter '{$name}' not registered.");
        }
        return self::$filters[$name];
    }

    /**
     * Check if a filter is registered.
     * 
     * @param string $name The name of the filter.
     * @return bool True if the filter is registered, false otherwise.
     */
    public static function has(string $name): bool
    {
        return isset(self::$filters[$name]);
    }

    /**
     * Unregister a filter.
     * 
     * @param string $name The name of the filter to unregister.
     * @return void
     * @throws FilterNotFoundException If the filter is not registered.
     */
    public static function unregister(string $name): void
    {
        if (!isset(self::$filters[$name])) {
            throw new FilterNotFoundException("Filter '{$name}' not registered.");
        }

        unset(self::$filters[$name]);
    }
}

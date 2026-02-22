<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

use Negarity\Color\Exception\GeneratorNotFoundException;

final class GeneratorRegistry
{
    /**
     * @var array<string, GeneratorInterface>
     */
    private static array $generators = [];

    /**
     * Register a generator.
     */
    public static function register(GeneratorInterface $generator): void
    {
        self::$generators[$generator->getName()] = $generator;
    }

    /**
     * Get a generator by name.
     *
     * @throws GeneratorNotFoundException If the generator is not registered.
     */
    public static function get(string $name): GeneratorInterface
    {
        if (!isset(self::$generators[$name])) {
            throw new GeneratorNotFoundException("Generator '{$name}' not registered.");
        }
        return self::$generators[$name];
    }

    /**
     * Check if a generator is registered.
     */
    public static function has(string $name): bool
    {
        return isset(self::$generators[$name]);
    }

    /**
     * Unregister a generator.
     *
     * @throws GeneratorNotFoundException If the generator is not registered.
     */
    public static function unregister(string $name): void
    {
        if (!isset(self::$generators[$name])) {
            throw new GeneratorNotFoundException("Generator '{$name}' not registered.");
        }
        unset(self::$generators[$name]);
    }
}

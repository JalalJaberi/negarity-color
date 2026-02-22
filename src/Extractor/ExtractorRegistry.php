<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\Exception\ExtractorNotFoundException;

final class ExtractorRegistry
{
    /**
     * @var array<string, ExtractorInterface>
     */
    private static array $extractors = [];

    public static function register(ExtractorInterface $extractor): void
    {
        self::$extractors[$extractor->getName()] = $extractor;
    }

    /**
     * @throws ExtractorNotFoundException
     */
    public static function get(string $name): ExtractorInterface
    {
        if (!isset(self::$extractors[$name])) {
            throw new ExtractorNotFoundException("Extractor '{$name}' not registered.");
        }
        return self::$extractors[$name];
    }

    public static function has(string $name): bool
    {
        return isset(self::$extractors[$name]);
    }

    /**
     * @throws ExtractorNotFoundException
     */
    public static function unregister(string $name): void
    {
        if (!isset(self::$extractors[$name])) {
            throw new ExtractorNotFoundException("Extractor '{$name}' not registered.");
        }
        unset(self::$extractors[$name]);
    }
}

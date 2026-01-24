<?php

declare(strict_types=1);

namespace Negarity\Color\Registry;

use Negarity\Color\ColorSpace\ColorSpaceInterface;
use Negarity\Color\Exception\ColorSpaceNotFoundException;
use Negarity\Color\Exception\InvalidColorValueException;

final class ColorSpaceRegistry
{
    /**
     * @var array<string, class-string<ColorSpaceInterface>>
     */
    private static array $colorSpaces = [];

    /**
     * Register a color space.
     * 
     * @param class-string<ColorSpaceInterface> $colorSpace The color space class to register.
     * @return void
     * @throws \InvalidArgumentException If the class doesn't implement ColorSpaceInterface.
     */
    public static function register(string $colorSpace): void
    {
        if (!is_subclass_of($colorSpace, ColorSpaceInterface::class)) {
            throw new InvalidColorValueException(
                "Color space '{$colorSpace}' must implement " . ColorSpaceInterface::class
            );
        }

        $name = $colorSpace::getName();
        self::$colorSpaces[$name] = $colorSpace;
    }

    /**
     * Unregister a color space.
     * 
     * @param string $name The name of the color space to unregister.
     * @return void
     * @throws ColorSpaceNotFoundException If the color space is not registered.
     */
    public static function unregister(string $name): void
    {
        if (!isset(self::$colorSpaces[$name])) {
            throw new ColorSpaceNotFoundException("Color space '{$name}' not registered.");
        }

        unset(self::$colorSpaces[$name]);
    }

    /**
     * Get a color space class by name.
     * 
     * @param string $name The name of the color space.
     * @return class-string<ColorSpaceInterface> The color space class.
     * @throws ColorSpaceNotFoundException If the color space is not registered.
     */
    public static function get(string $name): string
    {
        if (!isset(self::$colorSpaces[$name])) {
            throw new ColorSpaceNotFoundException("Color space '{$name}' not registered.");
        }

        return self::$colorSpaces[$name];
    }

    /**
     * Check if a color space is registered.
     * 
     * @param string $name The name of the color space.
     * @return bool True if the color space is registered, false otherwise.
     */
    public static function has(string $name): bool
    {
        return isset(self::$colorSpaces[$name]);
    }

    /**
     * Get all registered color space names.
     * 
     * @return array<string> Array of color space names.
     */
    public static function getAll(): array
    {
        return array_keys(self::$colorSpaces);
    }

    /**
     * Get all registered color space classes.
     * 
     * @return array<string, class-string<ColorSpaceInterface>> Array of color space name => class mappings.
     */
    public static function getAllClasses(): array
    {
        return self::$colorSpaces;
    }

    /**
     * Register all built-in color spaces.
     * 
     * @return void
     */
    public static function registerBuiltIn(): void
    {
        $builtInColorSpaces = [
            \Negarity\Color\ColorSpace\RGB::class,
            \Negarity\Color\ColorSpace\RGBA::class,
            \Negarity\Color\ColorSpace\CMYK::class,
            \Negarity\Color\ColorSpace\HSL::class,
            \Negarity\Color\ColorSpace\HSLA::class,
            \Negarity\Color\ColorSpace\HSV::class,
            \Negarity\Color\ColorSpace\Lab::class,
            \Negarity\Color\ColorSpace\LCh::class,
            \Negarity\Color\ColorSpace\XYZ::class,
            \Negarity\Color\ColorSpace\YCbCr::class,
        ];

        foreach ($builtInColorSpaces as $colorSpace) {
            self::register($colorSpace);
        }
    }
}

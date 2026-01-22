---
title: Adding New Color Spaces
sidebar_position: 1
---

# Adding New Color Spaces

This guide shows you how to extend Negarity Color by adding custom color spaces using the pluggable registry system.

## Overview

To add a new color space, you need to:
1. Create a color space class implementing `ColorSpaceInterface`
2. Implement required conversion methods (`toRGB()` and `fromRGB()`)
3. Register the color space with `ColorSpaceRegistry`
4. Optionally implement additional conversion methods for direct conversions

Once registered, your color space will work seamlessly with all existing functionality:
- Factory methods (e.g., `Color::myCustom()`)
- Conversion methods (e.g., `$color->toMyCustom()`)
- Filters (via RGB fallback)
- Named colors

## Step 1: Create the Color Space Class

Create a new class extending `AbstractColorSpace`:

```php
<?php

namespace MyApp\ColorSpace;

use Negarity\Color\ColorSpace\AbstractColorSpace;
use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEObserver;

final class MyCustomColorSpace extends AbstractColorSpace
{
    public static function getName(): string
    {
        return 'mycustom'; // Lowercase, no spaces - must be unique
    }

    public static function getChannels(): array
    {
        return ['channel1', 'channel2', 'channel3'];
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['channel1', 'channel2', 'channel3'], true);
    }

    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'channel1', 'channel2', 'channel3' => 0.0,
            default => throw new InvalidColorValueException(
                "Channel '{$name}' does not exist in mycustom color space."
            ),
        };
    }

    public static function validateValue(string $channel, float $value): void
    {
        match ($channel) {
            'channel1', 'channel2', 'channel3' => static::assertRange(
                $value, 
                0.0, 
                100.0, 
                $channel
            ),
            default => throw new InvalidColorValueException(
                "Channel '{$channel}' does not exist in mycustom color space."
            ),
        };
    }

    /**
     * Convert from this color space to RGB.
     * This is REQUIRED for all color spaces.
     * 
     * @param array<string, float> $values Color values in this space
     * @param CIEIlluminant|null $illuminant Optional CIE illuminant (if supported)
     * @param CIEObserver|null $observer Optional CIE observer (if supported)
     * @return array<string, float> RGB values: ['r' => float, 'g' => float, 'b' => float]
     */
    public static function toRGB(
        array $values,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        $c1 = $values['channel1'] ?? 0.0;
        $c2 = $values['channel2'] ?? 0.0;
        $c3 = $values['channel3'] ?? 0.0;

        // Your conversion logic here
        // Example: simple linear conversion
        $r = max(0.0, min(255.0, $c1 * 2.55));
        $g = max(0.0, min(255.0, $c2 * 2.55));
        $b = max(0.0, min(255.0, $c3 * 2.55));

        return [
            'r' => $r,
            'g' => $g,
            'b' => $b,
        ];
    }

    /**
     * Convert from RGB to this color space.
     * This is REQUIRED for all color spaces.
     * 
     * @param array<string, float> $values RGB values: ['r' => float, 'g' => float, 'b' => float]
     * @param int $alpha Optional alpha channel (0-255, default: 255)
     * @param CIEIlluminant|null $illuminant Optional CIE illuminant (if supported)
     * @param CIEObserver|null $observer Optional CIE observer (if supported)
     * @return array<string, float> Color values in this space
     */
    public static function fromRGB(
        array $values,
        int $alpha = 255,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        $r = $values['r'] ?? 0.0;
        $g = $values['g'] ?? 0.0;
        $b = $values['b'] ?? 0.0;

        // Your conversion logic here
        // Example: simple linear conversion
        $c1 = max(0.0, min(100.0, $r / 2.55));
        $c2 = max(0.0, min(100.0, $g / 2.55));
        $c3 = max(0.0, min(100.0, $b / 2.55));

        return [
            'channel1' => $c1,
            'channel2' => $c2,
            'channel3' => $c3,
        ];
    }
}
```

### Important Notes

- **Channel values are always floats**: All channel values must be `float` type for precision
- **`toRGB()` and `fromRGB()` are required**: These methods enable automatic conversions through RGB
- **Clamp values**: Always clamp converted values to valid ranges to prevent invalid colors
- **Return types**: Both methods must return `array<string, float>`

## Step 2: Register the Color Space

Register your color space with the `ColorSpaceRegistry`:

```php
use Negarity\Color\Registry\ColorSpaceRegistry;
use MyApp\ColorSpace\MyCustomColorSpace;

// Register your custom color space
ColorSpaceRegistry::register(MyCustomColorSpace::class);
```

## Step 3: Use Your Color Space

Once registered, your color space works automatically:

```php
use Negarity\Color\Color;
use Negarity\Color\Registry\ColorSpaceRegistry;
use MyApp\ColorSpace\MyCustomColorSpace;

// Register the color space
ColorSpaceRegistry::register(MyCustomColorSpace::class);

// Create colors using factory method (works automatically!)
$color = Color::mycustom(50.0, 60.0, 70.0);

// Convert to other color spaces
$rgb = $color->toRGB();
$hsl = $color->toHSL();
$cmyk = $color->toCMYK();

// Convert from other color spaces
$fromRgb = Color::rgb(255, 100, 50)->toMyCustom();
$fromHsl = Color::hsl(210, 50, 40)->toMyCustom();

// Use with filters (automatically converts via RGB)
$brightened = $color->brightness(20);
```

## Step 4: Optional - Add Direct Conversion Methods

For better performance, you can add direct conversion methods to avoid going through RGB. These are optional but recommended:

```php
// In MyCustomColorSpace class

/**
 * Convert from HSL to this color space (direct conversion, optional).
 * If not implemented, conversion will go through RGB.
 */
public static function fromHSL(array $values): array
{
    // Direct conversion logic from HSL
    // ...
}

/**
 * Convert from this color space to HSL (direct conversion, optional).
 * If not implemented, conversion will go through RGB.
 */
public static function toHSL(array $values): array
{
    // Direct conversion logic to HSL
    // ...
}
```

When you implement these methods, they will be automatically used instead of the RGB fallback.

## CIE Standard Support (Optional)

If your color space supports CIE Standard Illuminants and Observers, implement these methods:

```php
public static function supportsIlluminant(): bool
{
    return true; // or false
}

public static function supportsObserver(): bool
{
    return true; // or false
}
```

Then use the `$illuminant` and `$observer` parameters in your `toRGB()` and `fromRGB()` methods.

## Complete Example

Here's a complete example for a hypothetical "XYZ2" color space:

```php
<?php

namespace MyApp\ColorSpace;

use Negarity\Color\ColorSpace\AbstractColorSpace;
use Negarity\Color\Exception\InvalidColorValueException;

final class XYZ2 extends AbstractColorSpace
{
    public static function getName(): string
    {
        return 'xyz2';
    }

    public static function getChannels(): array
    {
        return ['x', 'y', 'z'];
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['x', 'y', 'z'], true);
    }

    public static function getChannelDefaultValue(string $name): float
    {
        return match ($name) {
            'x', 'y', 'z' => 0.0,
            default => throw new InvalidColorValueException(
                "Channel '{$name}' does not exist in xyz2 color space."
            ),
        };
    }

    public static function validateValue(string $channel, float $value): void
    {
        if (!is_numeric($value)) {
            throw new InvalidColorValueException(
                "Channel '{$channel}' must be numeric."
            );
        }
    }

    public static function toRGB(
        array $values,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $x = $values['x'] ?? 0.0;
        $y = $values['y'] ?? 0.0;
        $z = $values['z'] ?? 0.0;

        // Your conversion matrix here
        // Example conversion (simplified)
        $r = max(0.0, min(255.0, $x * 2.55));
        $g = max(0.0, min(255.0, $y * 2.55));
        $b = max(0.0, min(255.0, $z * 2.55));

        return ['r' => $r, 'g' => $g, 'b' => $b];
    }

    public static function fromRGB(
        array $values,
        int $alpha = 255,
        ?\Negarity\Color\CIE\CIEIlluminant $illuminant = null,
        ?\Negarity\Color\CIE\CIEObserver $observer = null
    ): array {
        $r = $values['r'] ?? 0.0;
        $g = $values['g'] ?? 0.0;
        $b = $values['b'] ?? 0.0;

        // Your conversion matrix here
        // Example conversion (simplified)
        $x = max(0.0, min(100.0, $r / 2.55));
        $y = max(0.0, min(100.0, $g / 2.55));
        $z = max(0.0, min(100.0, $b / 2.55));

        return ['x' => $x, 'y' => $y, 'z' => $z];
    }
}
```

Register and use:

```php
use Negarity\Color\Color;
use Negarity\Color\Registry\ColorSpaceRegistry;
use MyApp\ColorSpace\XYZ2;

// Register
ColorSpaceRegistry::register(XYZ2::class);

// Use
$color = Color::xyz2(20.0, 30.0, 40.0);
$rgb = $color->toRGB();
```

## Important Notes

- **Color space names** must be lowercase and unique
- **Channel validation** is important for data integrity
- **Conversion accuracy** depends on your conversion algorithms
- **Default values** should represent "no color" or "black" for your space
- **Range validation** prevents invalid color values
- **All values are floats**: Channel values are always `float` type for precision
- **RGB is the hub**: All conversions go through RGB if direct methods aren't available

## Testing Your Color Space

```php
use Negarity\Color\Color;
use Negarity\Color\Registry\ColorSpaceRegistry;
use MyApp\ColorSpace\XYZ2;

// Register
ColorSpaceRegistry::register(XYZ2::class);

// Create color in your space
$color = Color::xyz2(50.0, 60.0, 70.0);

// Convert to RGB
$rgb = $color->toRGB();
echo $rgb; // "rgb(...)"

// Convert back
$xyz2 = $rgb->toXyz2();
echo $xyz2; // "xyz2(...)"

// Test round-trip conversion
$original = Color::xyz2(50.0, 60.0, 70.0);
$converted = $original->toRGB()->toXyz2();
// Values should be close (may have slight precision differences)
```

## See Also

- [Color Spaces Reference](/docs/references/color-spaces) - See existing color spaces
- [Converting Colors](/docs/basics/converting-colors) - Understand conversions
- [Color Space Registry API](/docs/references/methods) - Registry methods
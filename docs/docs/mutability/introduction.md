---
title: Immutable vs Mutable Colors
sidebar_position: 1
---

# Immutable vs Mutable Colors

Negarity Color provides two color classes: `Color` (immutable) and `MutableColor` (mutable). Understanding the difference helps you choose the right one for your use case.

## Default: Immutable Color

By default, the `Color` class is **immutable**, meaning that all operations return a new `Color` instance rather than modifying the existing one. This is the recommended approach for most use cases.

### Benefits of Immutability

- **Prevents accidental mutations**: Original colors remain unchanged
- **Easier to reason about**: No side effects from operations
- **Supports functional programming**: Pure functions without side effects
- **Thread-safe**: Can be safely shared between contexts
- **Predictable**: Methods always return new instances

### Example with Immutable Color

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);
$modified = $color->with(['r' => 200]); // Returns a NEW Color instance

echo $color; // Still "rgb(255, 100, 50)" - original unchanged
echo $modified; // "rgb(200, 100, 50)" - new instance
```

### All Operations Return New Instances

```php
$color = Color::rgb(255, 100, 50);

// Conversion returns new instance
$hsl = $color->toHSL();
echo $color; // Still "rgb(255, 100, 50)"
echo $hsl; // "hsl(15, 100, 60)"

// Modification returns new instance
$lighter = $color->with(['r' => 200, 'g' => 150]);
echo $color; // Still "rgb(255, 100, 50)"
echo $lighter; // "rgb(200, 150, 50)"
```

## MutableColor: When You Need Mutability

The `MutableColor` class provides a **mutable** alternative where operations modify the instance in place. This can be useful in specific scenarios.

### When to Use MutableColor

- **Performance**: When you need to perform many operations on a single color object
- **Memory efficiency**: When creating many new instances would be wasteful
- **Iterative modifications**: When you're repeatedly modifying the same color object
- **State management**: When the color object represents changing state

### Example with MutableColor

```php
use Negarity\Color\MutableColor;
use Negarity\Color\ColorSpace\RGB;

$color = new MutableColor(RGB::class, ['r' => 255, 'g' => 100, 'b' => 50]);
$color->with(['r' => 200]); // Modifies the SAME instance

echo $color; // "rgb(200, 100, 50)" - original was modified
```

### Using setChannel()

`MutableColor` provides a `setChannel()` method for direct channel modification:

```php
$color = new MutableColor(RGB::class, ['r' => 255, 'g' => 100, 'b' => 50]);

// Directly set channel values
$color->setChannel('r', 200);
$color->setChannel('g', 150);

echo $color; // "rgb(200, 150, 50)"
```

### Chaining with MutableColor

Since `MutableColor` methods return `$this`, you can chain operations:

```php
$color = new MutableColor(RGB::class, ['r' => 255, 'g' => 100, 'b' => 50]);

$color->with(['r' => 200])
      ->toHSL()
      ->with(['l' => 60])
      ->toRGB();

echo $color; // Modified through the chain
```

## Key Differences

| Feature | Color (Immutable) | MutableColor |
|---------|------------------|--------------|
| **Operations** | Returns new instance | Modifies in place |
| **`with()` method** | Returns new Color | Returns self (for chaining) |
| **`without()` method** | Returns new Color | Returns self (for chaining) |
| **Conversion methods** | Returns new Color | Returns self (modifies instance) |
| **`setChannel()`** | Not available | Available |
| **Memory usage** | Creates new objects | Reuses same object |
| **Thread safety** | Safe | Not thread-safe |
| **Side effects** | None | Modifies state |

## Practical Examples

### Immutable: Creating Color Variants

```php
use Negarity\Color\Color;

$baseColor = Color::rgb(255, 100, 50);

// Create multiple variants without affecting original
$variant1 = $baseColor->with(['r' => 200]);
$variant2 = $baseColor->with(['g' => 150]);
$variant3 = $baseColor->toHSL()->with(['l' => 70])->toRGB();

// Original is still unchanged
echo $baseColor; // "rgb(255, 100, 50)"
```

### Mutable: Iterative Color Processing

```php
use Negarity\Color\MutableColor;
use Negarity\Color\ColorSpace\RGB;

$color = new MutableColor(RGB::class, ['r' => 255, 'g' => 100, 'b' => 50]);

// Process color through multiple steps
for ($i = 0; $i < 10; $i++) {
    $color->toHSL()
          ->with(['l' => $color->getL() + 5])
          ->toRGB();
}

// Single object modified multiple times
echo $color;
```

### Mutable: Performance-Critical Operations

```php
use Negarity\Color\MutableColor;
use Negarity\Color\ColorSpace\RGB;

// When you need to modify a color many times
$color = new MutableColor(RGB::class, ['r' => 255, 'g' => 100, 'b' => 50]);

// Many operations without creating new objects
for ($i = 0; $i < 1000; $i++) {
    $color->setChannel('r', $color->getR() - 1);
    // Process color...
}
```

## Converting Between Types

You can convert between immutable and mutable colors:

```php
use Negarity\Color\Color;
use Negarity\Color\MutableColor;

// Start with immutable
$immutable = Color::rgb(255, 100, 50);

// Convert to mutable (create new MutableColor instance)
$mutable = new MutableColor(
    $immutable->getColorSpace(),
    $immutable->getChannels()
);

// Or start with mutable and create immutable
$mutable = new MutableColor(RGB::class, ['r' => 255, 'g' => 100, 'b' => 50]);
$immutable = Color::rgb($mutable->getR(), $mutable->getG(), $mutable->getB());
```

## Best Practices

### Use Immutable Color When:
- ✅ Building most applications
- ✅ Working with color palettes or collections
- ✅ Passing colors between functions
- ✅ Need thread safety
- ✅ Want predictable, side-effect-free code

### Use MutableColor When:
- ✅ Performance is critical and you're doing many operations
- ✅ Working with a single color that changes over time
- ✅ Memory is constrained
- ✅ You understand the implications of mutability

## Important Notes

- **Default to Immutable**: Unless you have a specific need for mutability, use the immutable `Color` class
- **No Mixing**: Don't mix mutable and immutable colors in the same workflow without understanding the implications
- **Thread Safety**: `MutableColor` is not thread-safe - don't share mutable color instances between threads
- **State Management**: Be careful with mutable colors in complex state management scenarios

## Next Steps

- Review the [Basics](/docs/basics/creating-colors) section for more on working with colors
- Learn about [Filters](/docs/filters/introduction) for color transformations
- Explore [Extending the Library](/docs/extending/color-spaces) to add custom functionality

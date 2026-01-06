---
title: Introduction to Named Colors
sidebar_position: 1
---

# Introduction to Named Colors

Named colors allow you to create colors using human-readable names like "red", "blue", or "navy" instead of numeric values. Negarity Color supports named colors through a flexible registry system.

## What are Named Colors?

Named colors are predefined color values associated with common color names. Instead of remembering that red is `rgb(255, 0, 0)`, you can simply use `Color::red()`.

## Available Color Registries

Negarity Color comes with the **VGA Named Colors** registry, which includes 16 standard VGA colors:

- `white`, `silver`, `gray`, `black`
- `red`, `maroon`
- `yellow`, `olive`
- `lime`, `green`
- `aqua`, `teal`
- `blue`, `navy`
- `fuchsia`, `purple`

Each named color is available in all supported color spaces (RGB, RGBA, CMYK, HSL, HSLA, HSV, Lab, LCh, XYZ, YCbCr).

## Using Named Colors

### Registering a Color Registry

Before you can use named colors, you need to register a color registry:

```php
use Negarity\Color\Color;
use Negarity\Color\Registry\VGANamedColors;

// Register the VGA named colors
Color::addRegistry(new VGANamedColors());
```

### Creating Colors by Name

Once registered, you can create colors using static method calls with the color name:

```php
// Create colors in RGB (default)
$red = Color::red();
$blue = Color::blue();
$green = Color::green();

echo $red; // "rgb(255, 0, 0)"
echo $blue; // "rgb(0, 0, 255)"
echo $green; // "rgb(0, 128, 0)"
```

### Specifying Color Space

You can specify which color space to use when creating named colors:

```php
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\ColorSpace\CMYK;

// Create in HSL
$redHsl = Color::red(HSL::class);
echo $redHsl; // "hsl(0, 100, 50)"

// Create in CMYK
$blueCmyk = Color::blue(CMYK::class);
echo $blueCmyk; // "cmyk(100, 100, 0, 0)"
```

### Working with Named Colors

Named colors are regular `Color` instances, so you can use all the same methods:

```php
$red = Color::red();

// Convert to different color spaces
$redHsl = $red->toHSL();
$redCmyk = $red->toCMYK();

// Modify the color
$lighterRed = $red->toHSL()->with(['l' => 70])->toRGB();

// Get channel values
echo $red->getR(); // 255
echo $red->getG(); // 0
echo $red->getB(); // 0

// Convert to hex
echo $red->toHex(); // "#FF0000"
```

## Examples

### Basic Usage

```php
use Negarity\Color\Color;
use Negarity\Color\Registry\VGANamedColors;

Color::addRegistry(new VGANamedColors());

// Create a palette using named colors
$primary = Color::blue();
$secondary = Color::green();
$accent = Color::red();
$background = Color::white();
$text = Color::black();
```

### Creating Color Variants

```php
Color::addRegistry(new VGANamedColors());

$baseColor = Color::blue();

// Create lighter and darker variants
$lightBlue = $baseColor->toHSL()->with(['l' => 70])->toRGB();
$darkBlue = $baseColor->toHSL()->with(['l' => 30])->toRGB();
```

### Working with Different Color Spaces

```php
Color::addRegistry(new VGANamedColors());

// Get named color in different spaces
$redRgb = Color::red();
$redHsl = Color::red(\Negarity\Color\ColorSpace\HSL::class);
$redCmyk = Color::red(\Negarity\Color\ColorSpace\CMYK::class);

// All represent the same color
echo $redRgb->toHex(); // "#FF0000"
echo $redHsl->toHex(); // "#FF0000"
echo $redCmyk->toHex(); // "#FF0000"
```

## Error Handling

If you try to use a named color that doesn't exist in the registry, an exception will be thrown:

```php
Color::addRegistry(new VGANamedColors());

try {
    $color = Color::magenta(); // Throws InvalidArgumentException
} catch (\InvalidArgumentException $e) {
    echo "Named color 'magenta' not found in registry\n";
}
```

## Registering Custom Color Registries

You can create and register your own color registries. See the [Extending the Library - Adding Color Names](/docs/extending/color-names) section for detailed instructions.

### Multiple Registries

You can register multiple registries. The library will search through them in the order they were registered:

```php
Color::addRegistry(new VGANamedColors());
Color::addRegistry(new MyCustomColors());

// Will search VGANamedColors first, then MyCustomColors
$color = Color::red();
```

## Benefits of Named Colors

- **Readability**: Code is more self-documenting with names like `Color::blue()` instead of `Color::rgb(0, 0, 255)`
- **Consistency**: Ensures you use the exact same color values across your application
- **Maintainability**: Change a color definition in one place (the registry) and it updates everywhere
- **Multi-space Support**: Each named color is available in all color spaces

## Next Steps

- Learn about [Mutability](/docs/mutability/introduction) - using mutable vs immutable colors
- Explore [Filters](/docs/filters/introduction) - applying transformations to colors
- See [Extending the Library](/docs/extending/color-names) - create your own color registries

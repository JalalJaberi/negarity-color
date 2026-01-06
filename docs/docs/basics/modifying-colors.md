---
title: Modifying Colors
sidebar_position: 4
---

# Modifying Colors

Negarity Color provides methods to modify colors by changing channel values. Since the default `Color` class is **immutable**, these methods return new color instances rather than modifying the original.

## Using with()

The `with()` method creates a new color with specified channel values changed:

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);

// Change a single channel
$modified = $color->with(['r' => 200]);
echo $color; // Still "rgb(255, 100, 50)" - original unchanged
echo $modified; // "rgb(200, 100, 50)" - new instance

// Change multiple channels
$modified = $color->with(['r' => 200, 'g' => 150]);
echo $modified; // "rgb(200, 150, 50)"
```

### Working with Different Color Spaces

The `with()` method works with any color space:

```php
// HSL
$hsl = Color::hsl(210, 50, 40);
$lighter = $hsl->with(['l' => 60]); // Increase lightness

// CMYK
$cmyk = Color::cmyk(0, 50, 100, 0);
$modified = $cmyk->with(['m' => 75, 'k' => 10]);

// RGBA
$rgba = Color::rgba(255, 100, 50, 255);
$transparent = $rgba->with(['a' => 128]); // Make semi-transparent
```

## Using without()

The `without()` method resets specified channels to their default values (usually 0):

```php
$color = Color::rgb(255, 100, 50);

// Remove red channel (set to 0)
$noRed = $color->without(['r']);
echo $noRed; // "rgb(0, 100, 50)"

// Remove multiple channels
$noRedGreen = $color->without(['r', 'g']);
echo $noRedGreen; // "rgb(0, 0, 50)"
```

### Default Values

Each color space defines default values for its channels. When using `without()`, channels are reset to these defaults:

```php
$hsl = Color::hsl(210, 50, 40);

// Reset saturation to 0
$noSaturation = $hsl->without(['s']);
echo $noSaturation; // "hsl(210, 0, 40)"
```

## Creating Color Variants

You can create variations of colors by combining `with()` and conversions:

### Lightening a Color

```php
$color = Color::rgb(255, 100, 50);

// Convert to HSL to adjust lightness
$hsl = $color->toHSL();
$lighter = Color::hsl(
    $hsl->getH(),
    $hsl->getS(),
    min(100, $hsl->getL() + 20) // Increase lightness by 20
);
$lighterRgb = $lighter->toRGB();
```

### Darkening a Color

```php
$color = Color::rgb(255, 100, 50);
$hsl = $color->toHSL();
$darker = Color::hsl(
    $hsl->getH(),
    $hsl->getS(),
    max(0, $hsl->getL() - 20) // Decrease lightness by 20
);
$darkerRgb = $darker->toRGB();
```

### Adjusting Saturation

```php
$color = Color::rgb(255, 100, 50);
$hsl = $color->toHSL();

// More saturated
$moreSaturated = Color::hsl(
    $hsl->getH(),
    min(100, $hsl->getS() + 20),
    $hsl->getL()
);

// Less saturated (more gray)
$lessSaturated = Color::hsl(
    $hsl->getH(),
    max(0, $hsl->getS() - 20),
    $hsl->getL()
);
```

### Creating Transparent Variants

```php
$color = Color::rgb(255, 100, 50);

// Create 50% transparent version
$transparent = $color->toRGBA(128);

// Create fully transparent version
$invisible = $color->toRGBA(0);
```

## Immutability

The `Color` class is immutable, meaning all operations return new instances:

```php
$original = Color::rgb(255, 100, 50);
$modified = $original->with(['r' => 200]);

// Original is unchanged
echo $original->getR(); // Still 255
echo $modified->getR(); // 200
```

This immutability ensures:
- **Safety**: Original colors are never accidentally modified
- **Predictability**: Methods always return new instances
- **Thread-safety**: Colors can be safely shared between contexts

## Chaining Operations

Since modification methods return new `Color` instances, you can chain operations:

```php
$color = Color::rgb(255, 100, 50)
    ->toHSL()
    ->with(['l' => 60])
    ->toRGB()
    ->toRGBA(200);
```

## Practical Examples

### Creating a Color Palette

```php
$baseColor = Color::rgb(255, 100, 50);

// Create lighter variants
$light1 = $baseColor->toHSL()->with(['l' => 70])->toRGB();
$light2 = $baseColor->toHSL()->with(['l' => 85])->toRGB();

// Create darker variants
$dark1 = $baseColor->toHSL()->with(['l' => 30])->toRGB();
$dark2 = $baseColor->toHSL()->with(['l' => 15])->toRGB();
```

### Adjusting Color Temperature

```php
$color = Color::rgb(255, 100, 50);
$hsl = $color->toHSL();

// Warmer (shift hue toward red/yellow)
$warmer = Color::hsl(
    max(0, $hsl->getH() - 30),
    $hsl->getS(),
    $hsl->getL()
);

// Cooler (shift hue toward blue)
$cooler = Color::hsl(
    min(360, $hsl->getH() + 30),
    $hsl->getS(),
    $hsl->getL()
);
```

### Creating Opacity Variants

```php
$baseColor = Color::rgb(255, 100, 50);

$variants = [];
for ($alpha = 255; $alpha >= 0; $alpha -= 51) {
    $variants[] = $baseColor->toRGBA($alpha);
}
// Creates colors with opacity: 100%, 80%, 60%, 40%, 20%, 0%
```

## Error Handling

If you try to modify a channel that doesn't exist, an exception will be thrown:

```php
$rgb = Color::rgb(255, 100, 50);

try {
    $rgb->with(['h' => 210]); // Throws InvalidArgumentException
} catch (\InvalidArgumentException $e) {
    echo "Channel 'h' doesn't exist in RGB color space\n";
}
```

## Mutable Colors

If you need to modify colors in place, consider using `MutableColor` instead. See the [Mutability section](/docs/mutability/introduction) for more information.

## Next Steps

Now that you understand the basics, explore:
- [Named Colors](/docs/named-colors/introduction) - Using predefined color names
- [Filters](/docs/filters/introduction) - Applying color transformations
- [Extractors & Analysis](/docs/extractors-analysis/introduction) - Extracting color information

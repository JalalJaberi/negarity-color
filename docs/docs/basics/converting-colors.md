---
title: Converting Colors
sidebar_position: 2
---

# Converting Colors

Negarity Color supports seamless conversion between all registered color spaces. You can convert any color to any other color space using the appropriate conversion method. Conversions work automatically through the color space registry system.

## Prerequisites

Before converting colors, ensure color spaces are registered:

```php
use Negarity\Color\Registry\ColorSpaceRegistry;

// Register all built-in color spaces
ColorSpaceRegistry::registerBuiltIn();
```

## Basic Conversion Methods

Each color space has a corresponding `to*()` method that converts the color to that space:

### Converting to RGB

```php
use Negarity\Color\Color;

$hsl = Color::hsl(210, 50, 40);
$rgb = $hsl->toRGB();
echo $rgb; // "rgb(51, 77, 102)"
```

### Converting to RGBA

Convert any color to RGBA, optionally specifying the alpha value:

```php
$rgb = Color::rgb(255, 100, 50);
$rgba = $rgb->toRGBA(); // Default alpha: 255 (fully opaque)
$rgba = $rgb->toRGBA(128); // Semi-transparent
```

### Converting to HSL

```php
$rgb = Color::rgb(255, 100, 50);
$hsl = $rgb->toHSL();
echo $hsl; // "hsl(15, 100, 60)"
```

### Converting to HSLA

```php
$rgb = Color::rgb(255, 100, 50);
$hsla = $rgb->toHSLA(); // Default alpha: 255
$hsla = $rgb->toHSLA(128); // Custom alpha
```

### Converting to HSV

```php
$rgb = Color::rgb(255, 100, 50);
$hsv = $rgb->toHSV();
echo $hsv; // "hsv(15, 80, 100)"
```

### Converting to CMYK

```php
$rgb = Color::rgb(255, 100, 50);
$cmyk = $rgb->toCMYK();
echo $cmyk; // "cmyk(0, 61, 80, 0)"
```

### Converting to Lab

```php
$rgb = Color::rgb(255, 100, 50);
$lab = $rgb->toLab();
echo $lab; // "lab(60, 45, 65)"
```

### Converting to LCh

```php
$rgb = Color::rgb(255, 100, 50);
$lch = $rgb->toLCh();
echo $lch; // "lch(60, 78, 55)"
```

### Converting to XYZ

```php
$rgb = Color::rgb(255, 100, 50);
$xyz = $rgb->toXYZ();
echo $xyz; // "xyz(24, 18, 3)"
```

### Converting to YCbCr

```php
$rgb = Color::rgb(255, 100, 50);
$ycbcr = $rgb->toYCbCr();
echo $ycbcr; // "ycbcr(142, 95, 180)"
```

## Chain Conversions

Since conversion methods return new `Color` instances, you can chain multiple conversions:

```php
$color = Color::rgb(255, 100, 50)
    ->toHSL()
    ->toCMYK()
    ->toLab();
```

## Converting Between Any Spaces

You can convert between any two color spaces, even if they're not directly related:

```php
// Convert HSL directly to CMYK
$hsl = Color::hsl(210, 50, 40);
$cmyk = $hsl->toCMYK();

// Convert Lab to HSV
$lab = Color::lab(50, 20, -30);
$hsv = $lab->toHSV();
```

The library automatically handles intermediate conversions (usually through RGB) to ensure accurate results.

## Practical Examples

### Working with Web Colors

Convert hex colors to different formats:

```php
$webColor = Color::hex('#3498db');
$rgb = $webColor->toRGB();
$hsl = $webColor->toHSL();
$cmyk = $webColor->toCMYK(); // For print
```

### Adjusting Colors in Different Spaces

Sometimes it's easier to work in a specific color space:

```php
// Create a color in RGB
$color = Color::rgb(255, 100, 50);

// Convert to HSL to adjust lightness
$hsl = $color->toHSL();
$lighter = Color::hsl($hsl->getH(), $hsl->getS(), $hsl->getL() + 20);

// Convert back to RGB
$lighterRgb = $lighter->toRGB();
```

### Print vs Screen Colors

Convert between RGB (screen) and CMYK (print):

```php
// Screen color
$screenColor = Color::rgb(255, 100, 50);

// Convert for print
$printColor = $screenColor->toCMYK();
echo $printColor; // "cmyk(0, 61, 80, 0)"
```

## Important Notes

- **Immutability**: All conversion methods return new `Color` instances. The original color remains unchanged.
- **Accuracy**: Conversions are mathematically accurate, but some color spaces have different gamuts, so some colors may not be perfectly representable in all spaces.
- **Default Values**: When converting to color spaces with alpha channels (RGBA, HSLA), the default alpha is 255 (fully opaque) unless specified.

## Next Steps

Learn more about:
- [Accessing color channels](/docs/basics/getting-channels)
- [Modifying colors](/docs/basics/modifying-colors)

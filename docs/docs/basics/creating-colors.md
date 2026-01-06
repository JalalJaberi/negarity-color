---
title: Creating Colors
sidebar_position: 1
---

# Creating Colors

Negarity Color provides multiple ways to create color instances. This guide covers all the available methods.

## Using Static Factory Methods

The easiest way to create colors is using the static factory methods on the `Color` class. These methods are available for all supported color spaces.

### RGB Colors

Create colors using Red, Green, and Blue values (0-255):

```php
use Negarity\Color\Color;

$red = Color::rgb(255, 0, 0);
$green = Color::rgb(0, 255, 0);
$blue = Color::rgb(0, 0, 255);
$custom = Color::rgb(255, 100, 50);
```

### RGBA Colors

Create colors with an alpha channel for transparency (0-255, where 255 is fully opaque):

```php
$opaque = Color::rgba(255, 100, 50, 255);
$semiTransparent = Color::rgba(255, 100, 50, 128);
$transparent = Color::rgba(255, 100, 50, 0);
```

### HSL Colors

Create colors using Hue (0-360), Saturation (0-100), and Lightness (0-100):

```php
$hsl = Color::hsl(210, 50, 40);
// Hue: 210° (blue), Saturation: 50%, Lightness: 40%
```

### HSLA Colors

HSL with alpha channel:

```php
$hsla = Color::hsla(210, 50, 40, 100);
// Hue: 210°, Saturation: 50%, Lightness: 40%, Alpha: 100%
```

### HSV Colors

Create colors using Hue (0-360), Saturation (0-100), and Value (0-100):

```php
$hsv = Color::hsv(210, 50, 40);
// Hue: 210°, Saturation: 50%, Value: 40%
```

### CMYK Colors

Create colors for print using Cyan, Magenta, Yellow, and Key (black) values (0-100):

```php
$cmyk = Color::cmyk(0, 50, 100, 0);
// Cyan: 0%, Magenta: 50%, Yellow: 100%, Black: 0%
```

### Lab Colors

Create colors in the CIELAB color space (Lightness: 0-100, a: -128 to 127, b: -128 to 127):

```php
$lab = Color::lab(50, 20, -30);
```

### LCh Colors

Create colors using Lightness (0-100), Chroma (0-100), and Hue (0-360):

```php
$lch = Color::lch(50, 30, 210);
```

### XYZ Colors

Create colors in the CIE XYZ color space:

```php
$xyz = Color::xyz(20, 30, 40);
```

### YCbCr Colors

Create colors in the YCbCr color space (used in video and digital photography):

```php
$ycbcr = Color::ycbcr(128, 100, 150);
```

## Creating from Hex Strings

You can create colors from hexadecimal color codes, which is common in web development:

```php
// 6-digit hex (RGB)
$color1 = Color::hex('#FF6432');
$color2 = Color::hex('FF6432'); // Hash is optional

// 3-digit hex (RGB shorthand)
$color3 = Color::hex('#F64'); // Equivalent to #FF6644

// 8-digit hex (RGBA)
$color4 = Color::hex('#FF643280'); // With alpha channel

// 4-digit hex (RGBA shorthand)
$color5 = Color::hex('#F648'); // With alpha channel
```

You can also specify the desired color space when creating from hex:

```php
$hslFromHex = Color::hex('#FF6432', \Negarity\Color\ColorSpace\HSL::class);
$cmykFromHex = Color::hex('#FF6432', \Negarity\Color\ColorSpace\CMYK::class);
```

## Using the Constructor

You can also create colors directly using the constructor with a color space class and channel values:

```php
use Negarity\Color\Color;
use Negarity\Color\ColorSpace\RGB;
use Negarity\Color\ColorSpace\HSL;

// RGB color
$color1 = new Color(RGB::class, ['r' => 255, 'g' => 100, 'b' => 50]);

// HSL color
$color2 = new Color(HSL::class, ['h' => 210, 's' => 50, 'l' => 40]);
```

This approach is useful when you need to create colors dynamically or when working with color spaces that don't have a dedicated factory method.

## String Representation

Colors automatically convert to a readable string format:

```php
$color = Color::rgb(255, 100, 50);
echo $color; // Outputs: "rgb(255, 100, 50)"

$hsl = Color::hsl(210, 50, 40);
echo $hsl; // Outputs: "hsl(210, 50, 40)"
```

## Converting to Hex

You can convert any color to a hex string:

```php
$color = Color::rgb(255, 100, 50);
echo $color->toHex(); // Outputs: "#FF6432"

$rgba = Color::rgba(255, 100, 50, 128);
echo $rgba->toHex(); // Outputs: "#FF643280"
```

## Next Steps

Now that you know how to create colors, learn about:
- [Converting between color spaces](/docs/basics/converting-colors)
- [Accessing color channels](/docs/basics/getting-channels)
- [Modifying colors](/docs/basics/modifying-colors)

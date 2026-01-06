---
title: Brightness Filter
sidebar_position: 2
---

# Brightness Filter

The Brightness filter adjusts the brightness of a color by adding or subtracting a value from its channels.

## Overview

The Brightness filter is a **parameterized filter** that takes a brightness adjustment value (typically -255 to 255) and applies it to the color. Positive values make the color brighter, negative values make it darker.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;

FilterRegistry::register(new BrightnessFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);

// Increase brightness by 20
$brighter = $color->brightness(20);

// Decrease brightness by 30
$darker = $color->brightness(-30);
```

### How It Works

The brightness filter adjusts colors differently depending on the color space:

- **RGB/RGBA**: Adds/subtracts the value directly to R, G, B channels (clamped to 0-255)
- **HSL/HSLA**: Adjusts the Lightness channel
- **HSV**: Adjusts the Value channel
- **CMYK**: Adjusts C, M, Y channels inversely (brightness increase = decrease in ink)
- **Lab**: Adjusts the L (Lightness) channel
- **LCh**: Adjusts the L (Lightness) channel
- **XYZ**: Adjusts all X, Y, Z channels
- **YCbCr**: Adjusts the Y (luminance) channel

## Examples

### Creating Light and Dark Variants

```php
$baseColor = Color::rgb(255, 100, 50);

// Create lighter variants
$light1 = $baseColor->brightness(30);
$light2 = $baseColor->brightness(60);
$light3 = $baseColor->brightness(90);

// Create darker variants
$dark1 = $baseColor->brightness(-30);
$dark2 = $baseColor->brightness(-60);
$dark3 = $baseColor->brightness(-90);
```

### Working with Different Color Spaces

```php
// RGB
$rgb = Color::rgb(255, 100, 50);
$brightRgb = $rgb->brightness(20);

// HSL
$hsl = Color::hsl(15, 100, 60);
$brightHsl = $hsl->brightness(20); // Adjusts lightness

// CMYK
$cmyk = Color::cmyk(0, 61, 80, 0);
$brightCmyk = $cmyk->brightness(20); // Reduces ink amounts
```

### Chaining with Other Filters

```php
$color = Color::rgb(255, 100, 50)
    ->brightness(20)
    ->saturation(10)
    ->contrast();
```

## Parameters

- **Value**: Integer between -255 and 255
  - Positive values: Increase brightness
  - Negative values: Decrease brightness
  - Zero: No change

## Notes

- The filter works with all supported color spaces
- Values are automatically clamped to valid ranges for each color space
- The original color is not modified (returns a new instance)

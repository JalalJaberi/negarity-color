---
title: Grayscale Filter
sidebar_position: 6
---

# Grayscale Filter

The Grayscale filter converts a color to grayscale by removing color information and keeping only the luminance.

## Overview

The Grayscale filter is a **unary filter** that converts any color to a grayscale (black and white) version by calculating the perceived brightness and applying it to all RGB channels.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Unary\GrayscaleFilter;

FilterRegistry::register(new GrayscaleFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);
$grayscale = $color->grayscale();
// Result: All RGB channels set to the same luminance value
```

### How It Works

The grayscale filter typically uses a weighted average to calculate luminance:
```
luminance = 0.299 * R + 0.587 * G + 0.114 * B
```

Then sets all RGB channels to this value:
```
R = G = B = luminance
```

## Examples

### Converting Colors to Grayscale

```php
$red = Color::rgb(255, 0, 0);
$green = Color::rgb(0, 255, 0);
$blue = Color::rgb(0, 0, 255);

$grayRed = $red->grayscale();
$grayGreen = $green->grayscale();
$grayBlue = $blue->grayscale();
```

### Creating Monochrome Palettes

```php
$colors = [
    Color::rgb(255, 100, 50),
    Color::rgb(50, 200, 100),
    Color::rgb(100, 50, 255),
];

$grayscalePalette = array_map(fn($c) => $c->grayscale(), $colors);
```

## Notes

- Works with all supported color spaces (converts to RGB first)
- Preserves the perceived brightness of the original color
- The original color is not modified (returns a new instance)
- Alpha channel (if present) is preserved

## Use Cases

- Creating black and white versions of colors
- Generating monochrome palettes
- Artistic effects
- Accessibility (ensuring sufficient contrast)

---
title: Invert Filter
sidebar_position: 5
---

# Invert Filter

The Invert filter inverts all color channels, creating a negative image effect.

## Overview

The Invert filter is a **unary filter** that reverses all color channel values. For RGB colors, this creates a photographic negative effect.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Unary\InvertFilter;

FilterRegistry::register(new InvertFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);
$inverted = $color->invert();
// Result: rgb(0, 155, 205)
```

### How It Works

The invert filter calculates each channel as:
```
invertedChannel = maxValue - channelValue
```

Where `maxValue` depends on the color space:
- **RGB/RGBA**: 255
- **HSL/HSLA**: 100 for S and L, 360 for H
- **HSV**: 100 for S and V, 360 for H
- **CMYK**: 100
- **Lab**: 100 for L, varies for a and b
- And so on for other color spaces

## Examples

### Creating Negative Effects

```php
$color = Color::rgb(255, 200, 100);
$negative = $color->invert();
// White becomes black, colors become their complements
```

### Inverting Grayscale

```php
$gray = Color::rgb(128, 128, 128);
$inverted = $gray->invert();
// Result: rgb(127, 127, 127) - similar but inverted
```

### Working with Different Color Spaces

```php
// RGB inversion
$rgb = Color::rgb(255, 100, 50);
$invertedRgb = $rgb->invert();

// HSL inversion
$hsl = Color::hsl(15, 100, 60);
$invertedHsl = $hsl->invert();
```

## Notes

- Works with all supported color spaces
- Each channel is inverted independently
- The original color is not modified (returns a new instance)
- Inverting twice returns the original color

## Use Cases

- Creating negative image effects
- Generating complementary colors
- Artistic color manipulation
- Testing color algorithms

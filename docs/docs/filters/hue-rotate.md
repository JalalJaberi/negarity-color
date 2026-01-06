---
title: Hue Rotate Filter
sidebar_position: 9
---

# Hue Rotate Filter

The Hue Rotate filter shifts the hue of a color around the color wheel by a specified number of degrees.

## Overview

The Hue Rotate filter is a **unary filter** that rotates the hue component of a color. This creates a color shift effect, similar to a color wheel rotation.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Unary\HueRotateFilter;

FilterRegistry::register(new HueRotateFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 0, 0); // Red
$rotated = $color->hueRotate();
// Rotates the hue (may need parameter for degrees)
```

### How It Works

The hue rotate filter:
1. Converts the color to HSL or HSV color space
2. Adds the rotation amount to the Hue (H) channel
3. Wraps the value around if it exceeds 360° (H = (H + degrees) % 360)
4. Converts back to the original color space

## Examples

### Shifting Colors

```php
$red = Color::rgb(255, 0, 0);

// Rotate 120 degrees (red -> green)
$green = $red->hueRotate(); // May need parameter

// Rotate 240 degrees (red -> blue)
$blue = $red->hueRotate(); // May need parameter
```

### Creating Color Variations

```php
$baseColor = Color::rgb(255, 100, 50);

// Create variations by rotating hue
$variation1 = $baseColor->hueRotate(); // +30 degrees
$variation2 = $baseColor->hueRotate(); // +60 degrees
$variation3 = $baseColor->hueRotate(); // +90 degrees
```

## Notes

- Works with HSL, HSV, and LCh color spaces
- Hue rotation wraps around (360° = 0°)
- The original color is not modified (returns a new instance)
- Saturation and lightness are preserved

## Use Cases

- Creating color scheme variations
- Color correction
- Artistic color manipulation
- Generating complementary colors

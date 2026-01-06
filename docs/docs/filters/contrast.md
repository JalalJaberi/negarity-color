---
title: Contrast Filter
sidebar_position: 7
---

# Contrast Filter

The Contrast filter adjusts the contrast of a color by expanding or compressing the difference between light and dark values.

## Overview

The Contrast filter is a **unary filter** that enhances or reduces the contrast of a color. Higher contrast makes colors more vibrant and distinct, while lower contrast makes them more muted.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Unary\ContrastFilter;

FilterRegistry::register(new ContrastFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);
$highContrast = $color->contrast();
// Increases the difference between light and dark areas
```

### How It Works

The contrast filter typically:
1. Converts the color to a working color space (often RGB or HSL)
2. Applies a contrast adjustment algorithm
3. Expands values above the midpoint and compresses values below (for increased contrast)
4. Or compresses values above and expands below (for decreased contrast)

## Examples

### Enhancing Color Contrast

```php
$mutedColor = Color::rgb(150, 150, 150);
$enhanced = $mutedColor->contrast();
// Makes the color more vibrant
```

### Creating High-Contrast Palettes

```php
$colors = [
    Color::rgb(200, 200, 200),
    Color::rgb(100, 100, 100),
];

$highContrast = array_map(fn($c) => $c->contrast(), $colors);
```

## Notes

- Works with all supported color spaces
- The original color is not modified (returns a new instance)
- Contrast adjustment algorithms may vary

## Use Cases

- Enhancing image colors
- Creating vibrant color palettes
- Improving color distinction
- Artistic color manipulation

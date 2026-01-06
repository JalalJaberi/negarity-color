---
title: Saturation Filter
sidebar_position: 8
---

# Saturation Filter

The Saturation filter adjusts the color saturation, making colors more or less vivid.

## Overview

The Saturation filter is a **unary filter** that modifies the saturation of a color. Increasing saturation makes colors more vibrant, while decreasing it makes them more gray/muted.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Unary\SaturationFilter;

FilterRegistry::register(new SaturationFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);
$moreSaturated = $color->saturation();
// Increases color saturation
```

### How It Works

The saturation filter typically:
1. Converts the color to HSL or HSV color space
2. Adjusts the Saturation (S) channel
3. Converts back to the original color space

For increased saturation: `S = min(100, S * factor)`
For decreased saturation: `S = max(0, S / factor)`

## Examples

### Adjusting Color Vividness

```php
$color = Color::rgb(255, 100, 50);

// Increase saturation (more vivid)
$vivid = $color->saturation();

// Decrease saturation (more muted)
$muted = $color->saturation(); // May need parameter for decrease
```

### Creating Muted Palettes

```php
$vibrantColors = [
    Color::rgb(255, 0, 0),
    Color::rgb(0, 255, 0),
    Color::rgb(0, 0, 255),
];

$mutedPalette = array_map(fn($c) => $c->saturation(), $vibrantColors);
```

## Notes

- Works best with HSL/HSV color spaces
- The original color is not modified (returns a new instance)
- Saturation values are clamped to valid ranges

## Use Cases

- Creating vibrant or muted color schemes
- Adjusting color intensity
- Artistic color manipulation
- Matching design requirements

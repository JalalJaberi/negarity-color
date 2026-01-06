---
title: Gamma Filter
sidebar_position: 10
---

# Gamma Filter

The Gamma filter applies gamma correction to adjust the brightness curve of a color, typically used for display calibration.

## Overview

The Gamma filter is a **unary filter** that applies gamma correction, which adjusts how colors are displayed to account for the non-linear response of displays and human vision.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Unary\GammaFilter;

FilterRegistry::register(new GammaFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);
$gammaCorrected = $color->gamma();
// Applies gamma correction (may need parameter for gamma value)
```

### How It Works

Gamma correction typically uses the formula:
```
corrected = (value / maxValue) ^ (1 / gamma) * maxValue
```

Where:
- `value` is the channel value
- `maxValue` is the maximum value for that channel (255 for RGB)
- `gamma` is the gamma value (typically 2.2 for sRGB)

## Examples

### Display Calibration

```php
$color = Color::rgb(200, 150, 100);

// Apply standard sRGB gamma (2.2)
$corrected = $color->gamma(); // May need parameter
```

### Adjusting Brightness Curve

```php
// Lower gamma (brighter midtones)
$bright = $color->gamma(); // gamma < 1.0

// Higher gamma (darker midtones)
$dark = $color->gamma(); // gamma > 1.0
```

## Notes

- Typically applied to RGB color spaces
- Gamma value of 2.2 is standard for sRGB
- The original color is not modified (returns a new instance)
- Used for display calibration and color accuracy

## Use Cases

- Display calibration
- Color accuracy correction
- Image processing
- Professional color work

---
title: Threshold Filter
sidebar_position: 12
---

# Threshold Filter

The Threshold filter converts a color to pure black or white based on a threshold value, creating a binary (two-color) effect.

## Overview

The Threshold filter is a **parameterized filter** that converts colors to either black or white based on whether they're above or below a threshold value. This creates a high-contrast, binary effect.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\ThresholdFilter;

FilterRegistry::register(new ThresholdFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color = Color::rgb(200, 150, 100);

// Convert to black/white based on threshold
$threshold = $color->threshold(128); // Threshold value 0-255
```

### How It Works

The threshold filter:
1. Calculates the luminance of the color
2. Compares it to the threshold value
3. Returns black (0, 0, 0) if below threshold
4. Returns white (255, 255, 255) if above threshold

Formula:
```
luminance = 0.299 * R + 0.587 * G + 0.114 * B
if (luminance < threshold) {
    return black
} else {
    return white
}
```

## Examples

### Creating Binary Images

```php
$color = Color::rgb(200, 150, 100);

// Low threshold (more white)
$low = $color->threshold(100);

// Medium threshold
$medium = $color->threshold(128);

// High threshold (more black)
$high = $color->threshold(200);
```

### Edge Detection Effects

```php
$gray = Color::rgb(128, 128, 128);

// Create sharp black/white contrast
$binary = $gray->threshold(127);
```

## Parameters

- **Threshold**: Integer (0-255)
  - Lower values: More pixels become white
  - Higher values: More pixels become black
  - 128: Balanced threshold

## Notes

- Always returns pure black or white
- Uses luminance calculation for RGB colors
- The original color is not modified (returns a new instance)
- Useful for creating high-contrast binary images

## Use Cases

- Creating binary (black/white) images
- High-contrast effects
- Edge detection preprocessing
- Artistic black and white effects

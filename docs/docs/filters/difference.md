---
title: Difference Filter
sidebar_position: 13
---

# Difference Filter

The Difference filter calculates the absolute difference between two colors, useful for color comparison and analysis.

## Overview

The Difference filter is a **binary filter** that computes the absolute difference between corresponding channels of two colors. This is useful for measuring how similar or different two colors are.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Binary\DifferenceFilter;

FilterRegistry::register(new DifferenceFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color1 = Color::rgb(255, 100, 50);
$color2 = Color::rgb(200, 150, 100);

// Calculate difference
$difference = $color1->difference($color2);
// Result shows the absolute difference for each channel
```

### How It Works

The difference filter:
1. Checks that both colors are in the same color space
2. For each channel, calculates: `abs(baseChannel - blendChannel)`
3. Returns a new color with the difference values

## Examples

### Comparing Colors

```php
$red = Color::rgb(255, 0, 0);
$darkRed = Color::rgb(200, 0, 0);

// Calculate difference
$diff = $red->difference($darkRed);
// Result: rgb(55, 0, 0)
```

### Color Similarity Analysis

```php
$color1 = Color::rgb(255, 100, 50);
$color2 = Color::rgb(250, 105, 55);

$diff = $color1->difference($color2);
// Small differences indicate similar colors
```

### Working with Different Color Spaces

```php
// Both colors must be in the same space
$hsl1 = Color::hsl(0, 100, 50);
$hsl2 = Color::hsl(10, 90, 55);
$diff = $hsl1->difference($hsl2);
```

## Notes

- Both colors must be in the same color space
- Returns absolute differences (always positive)
- The original colors are not modified (returns a new instance)
- Useful for color comparison and analysis

## Use Cases

- Color comparison
- Similarity analysis
- Color matching algorithms
- Quality control in color processing

## See Also

- [Blend Filter](/docs/filters/blend) - Blend two colors
- [Mix Filter](/docs/filters/mix) - Mix colors with custom weight

---
title: Posterize Filter
sidebar_position: 11
---

# Posterize Filter

The Posterize filter reduces the number of color levels, creating a posterized effect with fewer distinct colors.

## Overview

The Posterize filter is a **parameterized filter** that reduces the number of color levels in an image, creating a stylized poster-like effect with distinct color bands.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\PosterizeFilter;

FilterRegistry::register(new PosterizeFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);

// Reduce to 4 levels per channel
$posterized = $color->posterize(4);
```

### How It Works

The posterize filter:
1. Divides the color range into a specified number of levels
2. Maps each channel value to the nearest level
3. Creates distinct color bands

Formula:
```
level = round((value / maxValue) * levels) * (maxValue / levels)
```

## Examples

### Creating Poster Effects

```php
$color = Color::rgb(200, 150, 100);

// Strong posterization (few levels)
$strong = $color->posterize(2);

// Moderate posterization
$moderate = $color->posterize(4);

// Subtle posterization
$subtle = $color->posterize(8);
```

### Artistic Effects

```php
$photoColor = Color::rgb(180, 120, 90);

// Create stylized poster effect
$poster = $photoColor->posterize(3);
```

## Parameters

- **Levels**: Integer (typically 2-256)
  - Lower values: More dramatic posterization
  - Higher values: More subtle effect
  - 256: No effect (full color range)

## Notes

- Works with all supported color spaces
- Each channel is posterized independently
- The original color is not modified (returns a new instance)
- Lower level counts create more dramatic effects

## Use Cases

- Artistic image effects
- Creating stylized graphics
- Reducing color complexity
- Vintage poster effects

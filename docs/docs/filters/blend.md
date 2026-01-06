---
title: Blend Filter
sidebar_position: 3
---

# Blend Filter

The Blend filter combines two colors by averaging their channel values (50/50 blend).

## Overview

The Blend filter is a **binary filter** that takes two colors and blends them together with equal weight (50% of each color). Both colors must be in the same color space.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Binary\BlendFilter;

FilterRegistry::register(new BlendFilter());
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color1 = Color::rgb(255, 0, 0);   // Red
$color2 = Color::rgb(0, 0, 255);  // Blue

// Blend the two colors (50/50)
$blended = $color1->blend($color2);
// Result: rgb(127, 0, 127) - Purple
```

### How It Works

The blend filter:
1. Checks that both colors are in the same color space
2. For each channel, calculates: `(baseChannel * 0.5) + (blendChannel * 0.5)`
3. Returns a new color with the blended channel values

## Examples

### Creating Color Mixtures

```php
// Mix primary colors
$red = Color::rgb(255, 0, 0);
$green = Color::rgb(0, 255, 0);
$blue = Color::rgb(0, 0, 255);

$yellow = $red->blend($green);      // rgb(127, 127, 0)
$cyan = $green->blend($blue);       // rgb(0, 127, 127)
$magenta = $red->blend($blue);      // rgb(127, 0, 127)
```

### Blending with White/Black

```php
$color = Color::rgb(255, 100, 50);

// Blend with white (lightens)
$lightened = $color->blend(Color::rgb(255, 255, 255));

// Blend with black (darkens)
$darkened = $color->blend(Color::rgb(0, 0, 0));
```

### Working with Different Color Spaces

```php
// Both colors must be in the same space
$hsl1 = Color::hsl(0, 100, 50);
$hsl2 = Color::hsl(120, 100, 50);
$blendedHsl = $hsl1->blend($hsl2);

// For different spaces, convert first
$rgb = Color::rgb(255, 0, 0);
$hsl = Color::hsl(240, 100, 50);
$blended = $rgb->blend($hsl->toRGB());
```

## Parameters

- **Blend Color**: A `ColorInterface` instance in the same color space as the base color

## Error Handling

If you try to blend colors from different color spaces, an exception is thrown:

```php
try {
    $rgb = Color::rgb(255, 0, 0);
    $hsl = Color::hsl(240, 100, 50);
    $blended = $rgb->blend($hsl); // Throws InvalidArgumentException
} catch (\InvalidArgumentException $e) {
    echo "Colors must be in the same color space\n";
}
```

## Notes

- Both colors must be in the same color space
- The blend is always 50/50 (equal weight)
- For custom blend ratios, use the [Mix filter](/docs/filters/mix)
- The original colors are not modified (returns a new instance)

## See Also

- [Mix Filter](/docs/filters/mix) - Blend with custom weight
- [Difference Filter](/docs/filters/difference) - Calculate color difference

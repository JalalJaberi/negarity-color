---
title: Mix Filter
sidebar_position: 4
---

# Mix Filter

The Mix filter combines two colors with a custom weight ratio, giving you control over how much of each color appears in the result.

## Overview

The Mix filter is a **binary filter** that takes two colors and a weight value (0.0 to 1.0) to control the blend ratio. A weight of 0.0 returns the base color, 1.0 returns the blend color, and 0.5 is a 50/50 mix.

## Registration

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Binary\MixFilter;

// Register with default weight (0.5 = 50/50)
FilterRegistry::register(new MixFilter());

// Or register with custom weight
FilterRegistry::register(new MixFilter(0.3)); // 30% blend, 70% base
```

## Usage

### Basic Usage

```php
use Negarity\Color\Color;

$color1 = Color::rgb(255, 0, 0);   // Red
$color2 = Color::rgb(0, 0, 255);   // Blue

// Mix with default 50/50 ratio
$mixed = $color1->mix($color2);

// The weight is set when registering the filter
// To use different weights, register multiple instances with different names
```

### How It Works

The mix filter calculates each channel as:
```
resultChannel = (1 - weight) * baseChannel + weight * blendChannel
```

Where:
- `weight = 0.0`: Result is 100% base color
- `weight = 0.5`: Result is 50% base, 50% blend
- `weight = 1.0`: Result is 100% blend color

## Examples

### Custom Blend Ratios

```php
// Register different mix filters with different weights
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Binary\MixFilter;

// Light mix (20% blend color)
FilterRegistry::register(new MixFilter(0.2));

// Medium mix (50% blend color)
FilterRegistry::register(new MixFilter(0.5));

// Heavy mix (80% blend color)
FilterRegistry::register(new MixFilter(0.8));

$base = Color::rgb(255, 0, 0);
$blend = Color::rgb(0, 0, 255);

$lightMix = $base->mix($blend); // 20% blue, 80% red
```

### Creating Color Gradients

```php
$start = Color::rgb(255, 0, 0);   // Red
$end = Color::rgb(0, 0, 255);      // Blue

// Create gradient steps
$steps = [];
for ($i = 0; $i <= 10; $i++) {
    $weight = $i / 10;
    // Register a mix filter with this weight
    // Then apply it
}
```

### Tinting Colors

```php
$baseColor = Color::rgb(200, 200, 200); // Gray
$tintColor = Color::rgb(255, 200, 100); // Warm tint

// Light tint (10% tint color)
FilterRegistry::register(new MixFilter(0.1));
$tinted = $baseColor->mix($tintColor);
```

## Parameters

- **Blend Color**: A `ColorInterface` instance in the same color space as the base color
- **Weight**: Set when registering the filter (0.0 to 1.0)
  - 0.0 = 100% base color
  - 0.5 = 50/50 mix
  - 1.0 = 100% blend color

## Error Handling

If you try to mix colors from different color spaces, an exception is thrown:

```php
try {
    $rgb = Color::rgb(255, 0, 0);
    $hsl = Color::hsl(240, 100, 50);
    $mixed = $rgb->mix($hsl); // Throws InvalidArgumentException
} catch (\InvalidArgumentException $e) {
    echo "Colors must be in the same color space\n";
}
```

## Notes

- Both colors must be in the same color space
- The weight is set when registering the filter instance
- For different weights, register multiple filter instances
- The original colors are not modified (returns a new instance)
- Weight values are automatically clamped to 0.0-1.0 range

## Comparison with Blend Filter

| Feature | Blend Filter | Mix Filter |
|--------|--------------|------------|
| Ratio | Fixed 50/50 | Custom weight |
| Flexibility | Simple | More control |
| Use Case | Quick blends | Precise mixing |

## See Also

- [Blend Filter](/docs/filters/blend) - Simple 50/50 blend
- [Difference Filter](/docs/filters/difference) - Calculate color difference

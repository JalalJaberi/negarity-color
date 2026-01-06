---
title: Introduction to Filters
sidebar_position: 1
---

# Introduction to Filters

Filters are transformations that you can apply to colors to modify their appearance. Negarity Color provides a flexible filter system that supports three types of filters: **Unary**, **Parameterized**, and **Binary**.

## What are Filters?

Filters are operations that take one or more colors and return a modified color. They can be used to:
- Adjust brightness, contrast, or saturation
- Apply artistic effects like grayscale or inversion
- Blend or mix colors together
- Apply advanced transformations like gamma correction

## Types of Filters

### Unary Filters

Unary filters operate on a single color and don't require any additional parameters:

```php
use Negarity\Color\Color;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Unary\InvertFilter;

// Register the filter
FilterRegistry::register(new InvertFilter());

// Apply the filter
$color = Color::rgb(255, 100, 50);
$inverted = $color->invert();
```

Available unary filters:
- [Invert](/docs/filters/invert) - Inverts all color channels
- [Grayscale](/docs/filters/grayscale) - Converts color to grayscale
- [Contrast](/docs/filters/contrast) - Adjusts color contrast
- [Saturation](/docs/filters/saturation) - Adjusts color saturation
- [HueRotate](/docs/filters/hue-rotate) - Rotates the hue
- [Gamma](/docs/filters/gamma) - Applies gamma correction

### Parameterized Filters

Parameterized filters take a single color and a parameter value:

```php
use Negarity\Color\Color;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;

// Register the filter
FilterRegistry::register(new BrightnessFilter());

// Apply with a parameter
$color = Color::rgb(255, 100, 50);
$brighter = $color->brightness(20); // Increase brightness by 20
```

Available parameterized filters:
- [Brightness](/docs/filters/brightness) - Adjusts brightness
- [Posterize](/docs/filters/posterize) - Reduces color levels
- [Threshold](/docs/filters/threshold) - Converts to black/white based on threshold

### Binary Filters

Binary filters operate on two colors:

```php
use Negarity\Color\Color;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Binary\BlendFilter;

// Register the filter
FilterRegistry::register(new BlendFilter());

// Apply with two colors
$color1 = Color::rgb(255, 0, 0);
$color2 = Color::rgb(0, 0, 255);
$blended = $color1->blend($color2);
```

Available binary filters:
- [Blend](/docs/filters/blend) - Blends two colors (50/50)
- [Mix](/docs/filters/mix) - Mixes two colors with custom weight
- [Difference](/docs/filters/difference) - Calculates color difference

## Registering Filters

Before you can use a filter, you must register it with the `FilterRegistry`:

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;

// Register a single filter
FilterRegistry::register(new BrightnessFilter());

// Register multiple filters
FilterRegistry::register(new BrightnessFilter());
FilterRegistry::register(new InvertFilter());
FilterRegistry::register(new BlendFilter());
```

## Applying Filters

Once registered, filters can be applied in two ways:

### Method 1: Using Color Methods (Recommended)

Filters are automatically available as methods on color objects:

```php
$color = Color::rgb(255, 100, 50);

// Unary filter
$inverted = $color->invert();

// Parameterized filter
$brighter = $color->brightness(20);

// Binary filter
$blended = $color->blend($otherColor);
```

### Method 2: Using FilterRegistry Directly

You can also apply filters directly through the registry:

```php
use Negarity\Color\Filter\FilterRegistry;

$color = Color::rgb(255, 100, 50);

// Unary
$inverted = FilterRegistry::get('invert')->apply($color);

// Parameterized
$brighter = FilterRegistry::get('brightness')->apply($color, 20);

// Binary
$blended = FilterRegistry::get('blend')->apply($color, $otherColor);
```

## Chaining Filters

Since filters return `Color` instances, you can chain multiple filters:

```php
$color = Color::rgb(255, 100, 50)
    ->brightness(20)
    ->invert()
    ->grayscale();
```

## Color Space Support

Most filters work with all color spaces. The filter automatically handles conversions when needed:

```php
$rgb = Color::rgb(255, 100, 50);
$hsl = Color::hsl(15, 100, 60);

// Both work the same way
$brightRgb = $rgb->brightness(20);
$brightHsl = $hsl->brightness(20);
```

## Examples

### Basic Filter Application

```php
use Negarity\Color\Color;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;
use Negarity\Color\Filter\Unary\InvertFilter;

// Register filters
FilterRegistry::register(new BrightnessFilter());
FilterRegistry::register(new InvertFilter());

// Apply filters
$color = Color::rgb(255, 100, 50);
$brighter = $color->brightness(30);
$inverted = $brighter->invert();
```

### Creating Color Effects

```php
// Vintage effect
$color = Color::rgb(255, 100, 50)
    ->saturation(-20)
    ->brightness(-10);

// High contrast effect
$color = Color::rgb(255, 100, 50)
    ->contrast()
    ->brightness(15);
```

## Next Steps

Explore individual filters:
- [Unary Filters](/docs/filters/invert) - Single-color transformations
- [Parameterized Filters](/docs/filters/brightness) - Filters with parameters
- [Binary Filters](/docs/filters/blend) - Two-color operations

Or learn how to [create custom filters](/docs/extending/filters).

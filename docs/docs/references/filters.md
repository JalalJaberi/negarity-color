---
title: Filters Reference
sidebar_position: 3
---

# Filters Reference

Complete reference for all available filters in Negarity Color.

## Unary Filters

Unary filters operate on a single color without additional parameters.

| Filter | Class | Description | Documentation |
|--------|-------|-------------|---------------|
| **Invert** | `InvertFilter` | Inverts all color channels | [Invert Filter](/docs/filters/invert) |
| **Grayscale** | `GrayscaleFilter` | Converts to grayscale | [Grayscale Filter](/docs/filters/grayscale) |
| **Contrast** | `ContrastFilter` | Adjusts color contrast | [Contrast Filter](/docs/filters/contrast) |
| **Saturation** | `SaturationFilter` | Adjusts color saturation | [Saturation Filter](/docs/filters/saturation) |
| **HueRotate** | `HueRotateFilter` | Rotates hue around color wheel | [Hue Rotate Filter](/docs/filters/hue-rotate) |
| **Gamma** | `GammaFilter` | Applies gamma correction | [Gamma Filter](/docs/filters/gamma) |

### Usage Example

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Unary\InvertFilter;

FilterRegistry::register(new InvertFilter());
$inverted = $color->invert();
```

## Parameterized Filters

Parameterized filters take a single color and a parameter value.

| Filter | Class | Parameter | Description | Documentation |
|--------|-------|-----------|-------------|---------------|
| **Brightness** | `BrightnessFilter` | `int` (-255 to 255) | Adjusts brightness | [Brightness Filter](/docs/filters/brightness) |
| **Posterize** | `PosterizeFilter` | `int` (levels) | Reduces color levels | [Posterize Filter](/docs/filters/posterize) |
| **Threshold** | `ThresholdFilter` | `int` (0-255) | Converts to black/white | [Threshold Filter](/docs/filters/threshold) |

### Usage Example

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;

FilterRegistry::register(new BrightnessFilter());
$brighter = $color->brightness(20);
```

## Binary Filters

Binary filters operate on two colors.

| Filter | Class | Parameters | Description | Documentation |
|--------|-------|------------|-------------|---------------|
| **Blend** | `BlendFilter` | `ColorInterface` | 50/50 blend of two colors | [Blend Filter](/docs/filters/blend) |
| **Mix** | `MixFilter` | `ColorInterface` | Custom weight blend | [Mix Filter](/docs/filters/mix) |
| **Difference** | `DifferenceFilter` | `ColorInterface` | Calculates color difference | [Difference Filter](/docs/filters/difference) |

### Usage Example

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Binary\BlendFilter;

FilterRegistry::register(new BlendFilter());
$blended = $color1->blend($color2);
```

## Filter Registration

All filters must be registered before use:

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;
use Negarity\Color\Filter\Unary\InvertFilter;
use Negarity\Color\Filter\Binary\BlendFilter;

// Register filters
FilterRegistry::register(new BrightnessFilter());
FilterRegistry::register(new InvertFilter());
FilterRegistry::register(new BlendFilter());
```

## Applying Filters

Once registered, filters can be applied in two ways:

### Method 1: Color Methods (Recommended)

```php
$color = Color::rgb(255, 100, 50);
$brighter = $color->brightness(20);
$inverted = $color->invert();
$blended = $color->blend($otherColor);
```

### Method 2: FilterRegistry

```php
$brighter = FilterRegistry::get('brightness')->apply($color, 20);
$inverted = FilterRegistry::get('invert')->apply($color);
$blended = FilterRegistry::get('blend')->apply($color, $otherColor);
```

## Chaining Filters

Filters can be chained since they return `Color` instances:

```php
$result = $color
    ->brightness(20)
    ->invert()
    ->grayscale();
```

## Color Space Support

Most filters work with all color spaces. The filter automatically handles conversions when needed.

## Filter Interfaces

- `FilterInterface` - Base interface for all filters
- `UnaryColorFilterInterface` - For unary filters
- `ParameterizedColorFilterInterface` - For parameterized filters
- `BinaryColorFilterInterface` - For binary filters

## See Also

- [Introduction to Filters](/docs/filters/introduction) - Overview of the filter system
- [Adding Filters](/docs/extending/filters) - How to create custom filters

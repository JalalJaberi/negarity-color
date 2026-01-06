---
title: Adding Filters
sidebar_position: 4
---

# Adding Filters

This guide shows you how to create custom filters for color transformations.

## Overview

There are three types of filters you can create:
- **Unary**: Operates on a single color
- **Parameterized**: Operates on a color with a parameter
- **Binary**: Operates on two colors

## Step 1: Choose Your Filter Type

### Unary Filter

For operations that only need one color:

```php
use Negarity\Color\Filter\Unary\UnaryColorFilterInterface;
use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\FilterInterface;

class MyUnaryFilter implements UnaryColorFilterInterface
{
    public function getName(): string
    {
        return 'myunary';
    }
    
    public function apply(ColorInterface $color): ColorInterface
    {
        // Your transformation logic
        return $transformedColor;
    }
}
```

### Parameterized Filter

For operations that need a parameter:

```php
use Negarity\Color\Filter\Parameterized\ParameterizedColorFilterInterface;
use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\FilterInterface;

class MyParameterizedFilter implements ParameterizedColorFilterInterface
{
    public function getName(): string
    {
        return 'myparameterized';
    }
    
    public function apply(ColorInterface $color, mixed $value): ColorInterface
    {
        // Use $value as your parameter
        return $transformedColor;
    }
}
```

### Binary Filter

For operations that need two colors:

```php
use Negarity\Color\Filter\Binary\BinaryColorFilterInterface;
use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\FilterInterface;

class MyBinaryFilter implements BinaryColorFilterInterface
{
    public function getName(): string
    {
        return 'mybinary';
    }
    
    public function apply(ColorInterface $base, ColorInterface $blend): ColorInterface
    {
        // Combine $base and $blend
        return $resultColor;
    }
}
```

## Step 2: Implement Your Filter

### Example: Sepia Filter (Unary)

```php
<?php

namespace MyApp\ColorFilter;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\Unary\UnaryColorFilterInterface;

final class SepiaFilter implements UnaryColorFilterInterface
{
    public function getName(): string
    {
        return 'sepia';
    }
    
    public function apply(ColorInterface $color): ColorInterface
    {
        $rgb = $color->toRGB();
        
        $r = $rgb->getR();
        $g = $rgb->getG();
        $b = $rgb->getB();
        
        // Sepia transformation matrix
        $newR = min(255, (int)(($r * 0.393) + ($g * 0.769) + ($b * 0.189)));
        $newG = min(255, (int)(($r * 0.349) + ($g * 0.686) + ($b * 0.168)));
        $newB = min(255, (int)(($r * 0.272) + ($g * 0.534) + ($b * 0.131)));
        
        return Color::rgb($newR, $newG, $newB);
    }
}
```

### Example: Opacity Filter (Parameterized)

```php
<?php

namespace MyApp\ColorFilter;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\Parameterized\ParameterizedColorFilterInterface;

final class OpacityFilter implements ParameterizedColorFilterInterface
{
    public function getName(): string
    {
        return 'opacity';
    }
    
    public function apply(ColorInterface $color, mixed $value): ColorInterface
    {
        $alpha = (int)$value;
        $alpha = max(0, min(255, $alpha)); // Clamp to 0-255
        
        return $color->toRGBA($alpha);
    }
}
```

### Example: Multiply Filter (Binary)

```php
<?php

namespace MyApp\ColorFilter;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\Binary\BinaryColorFilterInterface;

final class MultiplyFilter implements BinaryColorFilterInterface
{
    public function getName(): string
    {
        return 'multiply';
    }
    
    public function apply(ColorInterface $base, ColorInterface $blend): ColorInterface
    {
        if ($base->getColorSpace() !== $blend->getColorSpace()) {
            throw new \InvalidArgumentException(
                'Colors must be in the same color space to multiply.'
            );
        }
        
        $baseRgb = $base->toRGB();
        $blendRgb = $blend->toRGB();
        
        // Multiply each channel
        $r = (int)(($baseRgb->getR() * $blendRgb->getR()) / 255);
        $g = (int)(($baseRgb->getG() * $blendRgb->getG()) / 255);
        $b = (int)(($baseRgb->getB() * $blendRgb->getB()) / 255);
        
        return Color::rgb($r, $g, $b);
    }
}
```

## Step 3: Register Your Filter

Register your filter with the `FilterRegistry`:

```php
use Negarity\Color\Filter\FilterRegistry;
use MyApp\ColorFilter\SepiaFilter;
use MyApp\ColorFilter\OpacityFilter;
use MyApp\ColorFilter\MultiplyFilter;

// Register filters
FilterRegistry::register(new SepiaFilter());
FilterRegistry::register(new OpacityFilter());
FilterRegistry::register(new MultiplyFilter());
```

## Step 4: Use Your Filter

Once registered, use your filters:

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);

// Unary filter
$sepia = $color->sepia();

// Parameterized filter
$semiTransparent = $color->opacity(128);

// Binary filter
$otherColor = Color::rgb(200, 150, 100);
$multiplied = $color->multiply($otherColor);
```

## Handling Different Color Spaces

Your filter should handle different color spaces appropriately:

```php
public function apply(ColorInterface $color, mixed $value): ColorInterface
{
    switch ($color->getColorSpace()) {
        case RGB::class:
            // Handle RGB
            break;
        case HSL::class:
            // Handle HSL
            break;
        case CMYK::class:
            // Handle CMYK
            break;
        default:
            // Convert to RGB, process, convert back
            $rgb = $color->toRGB();
            $processed = $this->processRgb($rgb, $value);
            return $this->convertBack($processed, $color->getColorSpace());
    }
}
```

## Best Practices

- **Immutability**: Always return a new `Color` instance, don't modify the input
- **Color Space Support**: Handle multiple color spaces or convert appropriately
- **Validation**: Validate parameters and handle edge cases
- **Performance**: Consider the performance impact of conversions
- **Documentation**: Document what your filter does and its parameters

## Advanced: Filter with Configuration

You can create filters that accept configuration:

```php
final class CustomBrightnessFilter implements ParameterizedColorFilterInterface
{
    private float $factor;
    
    public function __construct(float $factor = 1.0)
    {
        $this->factor = $factor;
    }
    
    public function getName(): string
    {
        return 'custombrightness';
    }
    
    public function apply(ColorInterface $color, mixed $value): ColorInterface
    {
        $adjustment = (int)$value * $this->factor;
        // Apply adjustment...
    }
}

// Register with custom factor
FilterRegistry::register(new CustomBrightnessFilter(1.5));
```

## See Also

- [Introduction to Filters](/docs/filters/introduction) - Overview of filters
- [Filters Reference](/docs/references/filters) - Available filters
- [Brightness Filter](/docs/filters/brightness) - Example implementation

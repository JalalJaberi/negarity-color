---
title: Getting Started
sidebar_position: 0
---

# Getting Started

Welcome to Negarity Color! This guide will help you get started with the library by showing a simple application example.

## Installation

First, install the library using Composer:

```bash
composer require jalaljaberi/negarity-color
```

## Simple Application Example

Here's a practical example that demonstrates how to use Negarity Color to create, manipulate, and convert colors:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Registry\ColorSpaceRegistry;

// Register built-in color spaces (required before using them)
ColorSpaceRegistry::registerBuiltIn();

// Create a color from RGB values
$primaryColor = Color::rgb(255, 100, 50);
echo "Primary Color: " . $primaryColor . "\n";
echo "Hex: " . $primaryColor->toHex() . "\n";

// Convert to different color spaces
$hsl = $primaryColor->toHSL();
echo "HSL: " . $hsl . "\n";

$cmyk = $primaryColor->toCMYK();
echo "CMYK: " . $cmyk . "\n";

// Create a color from hex
$secondaryColor = Color::hex('#3498db');
echo "Secondary Color: " . $secondaryColor . "\n";
echo "RGB values: R={$secondaryColor->getR()}, G={$secondaryColor->getG()}, B={$secondaryColor->getB()}\n";

// Modify colors
$lighterColor = $primaryColor->with(['r' => 255, 'g' => 150, 'b' => 100]);
echo "Lighter variant: " . $lighterColor . "\n";

// Create a color with alpha channel
$transparentColor = Color::rgba(255, 100, 50, 128);
echo "Transparent Color: " . $transparentColor . "\n";
echo "Hex with alpha: " . $transparentColor->toHex() . "\n";

// Convert HSL to RGB
$hslColor = Color::hsl(210, 50, 40);
$rgbFromHsl = $hslColor->toRGB();
echo "HSL Color: " . $hslColor . "\n";
echo "Converted to RGB: " . $rgbFromHsl . "\n";
echo "Hex: " . $rgbFromHsl->toHex() . "\n";
```

## Output

When you run this example, you'll see output like:

```
Primary Color: rgb(255, 100, 50)
Hex: #FF6432
HSL: hsl(15, 100, 60)
CMYK: cmyk(0, 61, 80, 0)
Secondary Color: rgb(52, 152, 219)
RGB values: R=52, G=152, B=219
Lighter variant: rgb(255, 150, 100)
Transparent Color: rgba(255, 100, 50, 128)
Hex with alpha: #FF643280
HSL Color: hsl(210, 50, 40)
Converted to RGB: rgb(51, 102, 153)
Hex: #336699
```

## Key Features Demonstrated

- **Creating colors** from RGB values, hex strings, and HSL values
- **Converting between color spaces** (RGB, HSL, CMYK, RGBA)
- **Accessing color channels** using getter methods
- **Modifying colors** using the `with()` method
- **Converting to hex format** for use in web applications

This example shows the basic functionality of Negarity Color. The library supports many more features including filters, named colors, and additional color spaces like Lab, LCh, XYZ, and YCbCr.

## Next Steps

Now that you've seen a simple example, you can explore more:

- Learn the [basics](/docs/basics/creating-colors) of creating and working with colors
- Discover how to use [named colors](/docs/named-colors/introduction)
- Explore [extractors and analysis](/docs/extractors-analysis/introduction) features
- Apply [filters](/docs/filters/introduction) to transform colors
- Check the [complete reference](/docs/references/color-spaces) for all available features
- Learn how to [extend the library](/docs/extending/color-spaces) with custom functionality

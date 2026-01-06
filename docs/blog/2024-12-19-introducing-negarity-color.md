---
slug: introducing-negarity-color
title: Introducing Negarity Color - A Modern PHP Color Library
authors:
  - jalaljaberi
tags:
  - announcement
  - php
  - color
  - library
---

# Introducing Negarity Color

We're excited to announce **Negarity Color** — a modern, extensible color manipulation library for PHP 8.3+.

## What is Negarity Color?

Negarity Color is a comprehensive PHP library designed to make working with colors simple, powerful, and flexible. Whether you're building web applications, image processing tools, or design systems, Negarity Color provides everything you need to work with colors effectively.

## Key Features

### 🎨 Multiple Color Spaces

Negarity Color supports **10 color spaces** out of the box:

- **RGB/RGBA** - For screen and web applications
- **HSL/HSLA** - Intuitive color manipulation
- **HSV** - Color pickers and graphics software
- **CMYK** - Professional printing
- **Lab/LCh** - Perceptually uniform color matching
- **XYZ** - Color science and conversions
- **YCbCr** - Video and image compression

### 🔄 Seamless Conversions

Convert between any color space with a single method call:

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);
$hsl = $color->toHSL();
$cmyk = $color->toCMYK();
$lab = $color->toLab();
```

### 🎯 Immutable by Default

The library uses immutable color objects by default, making your code safer and more predictable:

```php
$color = Color::rgb(255, 100, 50);
$modified = $color->with(['r' => 200]);

echo $color; // Still "rgb(255, 100, 50)" - original unchanged
echo $modified; // "rgb(200, 100, 50)" - new instance
```

### 🎭 Flexible Filter System

Apply color transformations with a powerful filter system:

```php
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;
use Negarity\Color\Filter\Unary\InvertFilter;

FilterRegistry::register(new BrightnessFilter());
FilterRegistry::register(new InvertFilter());

$color = Color::rgb(255, 100, 50)
    ->brightness(20)
    ->invert();
```

### 🏷️ Named Colors

Use human-readable color names:

```php
use Negarity\Color\Color;
use Negarity\Color\Registry\VGANamedColors;

Color::addRegistry(new VGANamedColors());

$red = Color::red();
$blue = Color::blue();
$green = Color::green();
```

### 🔧 Fully Extensible

Easily extend the library with:
- Custom color spaces
- Custom color names
- Custom filters
- Custom extractors

## Quick Start

### Installation

```bash
composer require jalaljaberi/negarity-color
```

### Basic Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Negarity\Color\Color;

// Create colors
$primary = Color::rgb(255, 100, 50);
$secondary = Color::hex('#3498db');

// Convert between spaces
$hsl = $primary->toHSL();
$cmyk = $primary->toCMYK();

// Modify colors
$lighter = $primary->with(['r' => 255, 'g' => 150, 'b' => 100]);

// Get channel values
echo $primary->getR(); // 255
echo $primary->toHex(); // "#FF6432"
```

## Why Negarity Color?

### Modern PHP

Built for PHP 8.3+ with modern features like:
- Strict types
- Readonly properties
- Enums
- Attributes

### Well-Architected

- Clean, intuitive API
- PSR-4 autoloading
- Comprehensive error handling
- Full type safety

### Production Ready

- Extensively tested
- Well-documented
- MIT licensed
- Actively maintained

## Use Cases

Negarity Color is perfect for:

- **Web Development**: Color manipulation for themes, palettes, and UI
- **Image Processing**: Color transformations and analysis
- **Design Tools**: Color pickers, palette generators, and color harmony
- **Print Media**: CMYK conversions for professional printing
- **Data Visualization**: Color mapping and gradients
- **Accessibility**: Contrast analysis and WCAG compliance

## What's Next?

We're just getting started! Check out our [documentation](/docs/getting-started) to learn more:

- [Basics](/docs/basics/creating-colors) - Creating and converting colors
- [Named Colors](/docs/named-colors/introduction) - Using predefined colors
- [Filters](/docs/filters/introduction) - Applying transformations
- [Extending](/docs/extending/color-spaces) - Creating custom functionality

## Get Involved

Negarity Color is open source and we welcome contributions! Whether you want to:
- Report bugs
- Suggest features
- Submit pull requests
- Improve documentation

Your contributions help make Negarity Color better for everyone.

## Conclusion

Negarity Color brings modern color manipulation to PHP. With support for multiple color spaces, a flexible filter system, and a clean API, it's the perfect choice for any PHP project that needs to work with colors.

Try it out today and let us know what you think!

---

**Installation**: `composer require jalaljaberi/negarity-color`  
**Documentation**: [docs.negarity-color.com](/docs/getting-started)  
**GitHub**: [github.com/jalaljaberi/negarity-color](https://github.com/jalaljaberi/negarity-color)

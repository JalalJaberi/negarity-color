# Negarity Color

**Negarity Color** is a modern, extensible color manipulation library for **PHP 8.3+**.

## Installation

```bash
composer require jalaljaberi/negarity-color
```

## Basic Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Registry\ColorSpaceRegistry;

// Register built-in color spaces (required before use)
ColorSpaceRegistry::registerBuiltIn();

// Create colors
$primary = Color::rgb(255, 100, 50);
$secondary = Color::hex('#3498db');

// Convert between color spaces
$hsl = $primary->toHSL();
$cmyk = $primary->toCMYK();

// Read channels
$r = $primary->getR();        // 255
$space = $primary->getColorSpaceName(); // "rgb"

// Modify (immutable by default)
$lighter = $primary->with(['g' => 150]);

echo $primary->toHex();   // "#FF6432"
echo (string) $hsl;       // "hsl(...)"
```

## Configuration (Optional)

### Named colors

Register a named-color registry once, then use `Color::{name}()`:

```php
use Negarity\Color\Color;
use Negarity\Color\Registry\VGANamedColors;

Color::addRegistry(new VGANamedColors());

$red = Color::red();
$navyHsl = Color::navy(\Negarity\Color\ColorSpace\HSL::class);
```

### Color Spaces

Color spaces must be registered before use. Register all built-in color spaces:

```php
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
```

### Filters

Filters must be registered before they are available as methods on `Color` objects:

```php
use Negarity\Color\Color;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;
use Negarity\Color\Filter\Unary\InvertFilter;

FilterRegistry::register(new BrightnessFilter());
FilterRegistry::register(new InvertFilter());

$color = Color::rgb(255, 100, 50)
    ->brightness(20)
    ->invert();
```

## Documentation

[Getting started](https://jalaljaberi.github.io/negarity-color/docs/getting-started)

## References

- [Martin Krzywinski color table](https://mk.bcgsc.ca/colornames/) - A comprehensive list of 9,284 named colors with conversions across multiple color spaces (RGB, HSV, XYZ, Lab, LCH, CMYK)

## License

MIT


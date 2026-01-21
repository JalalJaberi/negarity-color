---
title: RGBA Color Space
sidebar_position: 2
---

# RGBA Color Space

**RGBA** (Red, Green, Blue, Alpha) extends RGB with an alpha channel for transparency. It's commonly used in web graphics, UI design, and any application where transparency is needed.

## Channels

RGBA has four channels:

- **`r`** (Red): Intensity of red light (0-255)
- **`g`** (Green): Intensity of green light (0-255)
- **`b`** (Blue): Intensity of blue light (0-255)
- **`a`** (Alpha): Transparency/opacity (0-255)

## Channel Ranges

- **RGB channels** (`r`, `g`, `b`): 0-255
- **Alpha channel** (`a`): 0-255
  - `0` = fully transparent
  - `255` = fully opaque

## Default Values

- **RGB channels**: Default to `0` (no color)
- **Alpha channel**: Defaults to `255` (fully opaque)

This means an RGBA color with only RGB specified will be fully opaque.

## Creating RGBA Colors

```php
use Negarity\Color\Color;

// Create an RGBA color with full opacity
$color = Color::rgba(255, 100, 50, 255);

// Create with 50% opacity
$semiTransparent = Color::rgba(255, 100, 50, 128);

// Create fully transparent
$transparent = Color::rgba(255, 100, 50, 0);
```

## Working with RGBA Channels

### Getting Channel Values

```php
$color = Color::rgba(255, 128, 64, 200);

$r = $color->getR();  // 255
$g = $color->getG();  // 128
$b = $color->getB();  // 64
$a = $color->getA();  // 200
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\RGBA;

RGBA::hasChannel('r');  // true
RGBA::hasChannel('g');  // true
RGBA::hasChannel('b');  // true
RGBA::hasChannel('a');  // true
RGBA::hasChannel('x');  // false
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\RGBA;

RGBA::getChannelDefaultValue('r');  // 0
RGBA::getChannelDefaultValue('g');  // 0
RGBA::getChannelDefaultValue('b');  // 0
RGBA::getChannelDefaultValue('a');  // 255 (fully opaque)
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\RGBA;

RGBA::getChannels();  // ['r', 'g', 'b', 'a']
```

## Validation

RGBA automatically validates all channel values:

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::rgba(300, 100, 50, 128);  // R value exceeds 255
} catch (InvalidColorValueException $e) {
    // Handle error
}

try {
    // This will also throw InvalidColorValueException
    $color = Color::rgba(255, 100, 50, 300);  // Alpha value exceeds 255
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Use Cases

- **Web Graphics**: CSS `rgba()` colors, transparent backgrounds
- **UI Design**: Overlays, shadows, glassmorphism effects
- **Image Editing**: Layers with transparency
- **Game Development**: Transparent sprites, UI elements
- **Data Visualization**: Semi-transparent data points

## Color Space Information

```php
use Negarity\Color\ColorSpace\RGBA;

RGBA::getName();  // "rgba"
RGBA::getChannels();  // ['r', 'g', 'b', 'a']
RGBA::supportsIlluminant();  // false
RGBA::supportsObserver();  // false
```

## See Also

- [RGB Color Space](/docs/references/color-spaces/rgb) - RGB without transparency
- [Creating Colors](/docs/basics/creating-colors) - How to create RGBA colors
- [Getting Channels](/docs/basics/getting-channels) - How to access RGBA channel values

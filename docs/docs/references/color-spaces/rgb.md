---
title: RGB Color Space
sidebar_position: 1
---

# RGB Color Space

**RGB** (Red, Green, Blue) is the most common color space for digital displays, web colors, and digital images. It's an additive color model where colors are created by combining different intensities of red, green, and blue light.

## Channels

RGB has three channels:

- **`r`** (Red): Intensity of red light
- **`g`** (Green): Intensity of green light
- **`b`** (Blue): Intensity of blue light

## Channel Ranges

All channels have the same range:
- **Minimum**: 0 (no color)
- **Maximum**: 255 (full intensity)

## Default Values

All channels default to `0` when not specified, resulting in black (`rgb(0, 0, 0)`).

## Creating RGB Colors

```php
use Negarity\Color\Color;

// Create an RGB color
$color = Color::rgb(255, 100, 50);

// Create with individual channel access
$red = Color::rgb(255, 0, 0);
$green = Color::rgb(0, 255, 0);
$blue = Color::rgb(0, 0, 255);
```

## Working with RGB Channels

### Getting Channel Values

```php
$color = Color::rgb(255, 128, 64);

$r = $color->getR();  // 255
$g = $color->getG();  // 128
$b = $color->getB();  // 64
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\RGB;

RGB::hasChannel('r');  // true
RGB::hasChannel('g');  // true
RGB::hasChannel('b');  // true
RGB::hasChannel('a');  // false (RGBA has alpha)
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\RGB;

RGB::getChannelDefaultValue('r');  // 0
RGB::getChannelDefaultValue('g');  // 0
RGB::getChannelDefaultValue('b');  // 0
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\RGB;

RGB::getChannels();  // ['r', 'g', 'b']
```

## Validation

RGB automatically validates channel values:

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::rgb(300, 100, 50);  // R value exceeds 255
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Use Cases

- **Web Development**: CSS colors, HTML color codes
- **Digital Images**: Most image formats use RGB
- **Screen Display**: All modern displays use RGB
- **Color Picking**: Most color pickers default to RGB
- **Gaming**: Game engines typically work in RGB

## Color Space Information

```php
use Negarity\Color\ColorSpace\RGB;

RGB::getName();  // "rgb"
RGB::getChannels();  // ['r', 'g', 'b']
RGB::supportsIlluminant();  // false
RGB::supportsObserver();  // false
```

## See Also

- [RGBA Color Space](/docs/references/color-spaces/rgba) - RGB with transparency
- [Creating Colors](/docs/basics/creating-colors) - How to create RGB colors
- [Getting Channels](/docs/basics/getting-channels) - How to access RGB channel values

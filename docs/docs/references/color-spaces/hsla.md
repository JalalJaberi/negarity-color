---
title: HSLA Color Space
sidebar_position: 4
---

# HSLA Color Space

**HSLA** (Hue, Saturation, Lightness, Alpha) extends HSL with an alpha channel for transparency. It combines the intuitive color manipulation of HSL with transparency support.

## Channels

HSLA has four channels:

- **`h`** (Hue): The color itself, 0-360 degrees
- **`s`** (Saturation): Color intensity, 0-100%
- **`l`** (Lightness): Brightness, 0-100%
- **`a`** (Alpha): Transparency/opacity, 0-255

## Channel Ranges

- **Hue (`h`)**: 0-360 degrees
- **Saturation (`s`)**: 0-100%
- **Lightness (`l`)**: 0-100%
- **Alpha (`a`)**: 0-255
  - `0` = fully transparent
  - `255` = fully opaque

## Default Values

- **HSL channels** (`h`, `s`, `l`): Default to `0`
- **Alpha channel** (`a`): Defaults to `255` (fully opaque)

## Creating HSLA Colors

```php
use Negarity\Color\Color;

// Create an HSLA color with full opacity
$color = Color::hsla(210, 50, 40, 255);

// Create with 50% opacity
$semiTransparent = Color::hsla(210, 50, 40, 128);

// Create fully transparent
$transparent = Color::hsla(210, 50, 40, 0);
```

## Working with HSLA Channels

### Getting Channel Values

```php
$color = Color::hsla(210, 75, 60, 200);

$h = $color->getH();  // 210
$s = $color->getS();  // 75
$l = $color->getL();  // 60
$a = $color->getA();  // 200
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\HSLA;

HSLA::hasChannel('h');  // true
HSLA::hasChannel('s');  // true
HSLA::hasChannel('l');  // true
HSLA::hasChannel('a');  // true
HSLA::hasChannel('v');  // false (that's HSV)
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\HSLA;

HSLA::getChannelDefaultValue('h');  // 0
HSLA::getChannelDefaultValue('s');  // 0
HSLA::getChannelDefaultValue('l');  // 0
HSLA::getChannelDefaultValue('a');  // 255 (fully opaque)
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\HSLA;

HSLA::getChannels();  // ['h', 's', 'l', 'a']
```

## Validation

HSLA automatically validates all channel values:

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::hsla(400, 50, 40, 255);  // Hue exceeds 360
} catch (InvalidColorValueException $e) {
    // Handle error
}

try {
    // This will also throw InvalidColorValueException
    $color = Color::hsla(210, 50, 40, 300);  // Alpha exceeds 255
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Use Cases

- **Web Design**: CSS `hsla()` colors with transparency
- **UI Components**: Transparent overlays with intuitive color adjustments
- **Themes**: Easy color variations with transparency
- **Data Visualization**: Semi-transparent data points with HSL-based colors
- **Design Tools**: Color manipulation with transparency support

## Color Space Information

```php
use Negarity\Color\ColorSpace\HSLA;

HSLA::getName();  // "hsla"
HSLA::getChannels();  // ['h', 's', 'l', 'a']
HSLA::supportsIlluminant();  // false
HSLA::supportsObserver();  // false
```

## See Also

- [HSL Color Space](/docs/references/color-spaces/hsl) - HSL without transparency
- [Creating Colors](/docs/basics/creating-colors) - How to create HSLA colors
- [Getting Channels](/docs/basics/getting-channels) - How to access HSLA channel values

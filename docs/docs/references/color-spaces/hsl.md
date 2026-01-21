---
title: HSL Color Space
sidebar_position: 3
---

# HSL Color Space

**HSL** (Hue, Saturation, Lightness) is an intuitive color space that's particularly useful for color manipulation and adjustments. It's designed to be more human-friendly than RGB, making it easier to create color variations and harmonies.

## Channels

HSL has three channels:

- **`h`** (Hue): The color itself, represented as an angle
- **`s`** (Saturation): The intensity or purity of the color
- **`l`** (Lightness): The brightness of the color

## Channel Ranges

- **Hue (`h`)**: 0-360 degrees
  - `0°` = Red
  - `120°` = Green
  - `240°` = Blue
  - `360°` = Red (wraps around)
- **Saturation (`s`)**: 0-100%
  - `0%` = Gray (no color)
  - `100%` = Full color intensity
- **Lightness (`l`)**: 0-100%
  - `0%` = Black
  - `50%` = Full color
  - `100%` = White

## Default Values

All channels default to `0` when not specified, resulting in black (`hsl(0, 0, 0)`).

## Creating HSL Colors

```php
use Negarity\Color\Color;

// Create an HSL color
$color = Color::hsl(210, 50, 40);

// Pure red
$red = Color::hsl(0, 100, 50);

// Gray (no saturation)
$gray = Color::hsl(0, 0, 50);
```

## Working with HSL Channels

### Getting Channel Values

```php
$color = Color::hsl(210, 75, 60);

$h = $color->getH();  // 210
$s = $color->getS();  // 75
$l = $color->getL();  // 60
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\HSL;

HSL::hasChannel('h');  // true
HSL::hasChannel('s');  // true
HSL::hasChannel('l');  // true
HSL::hasChannel('a');  // false (HSLA has alpha)
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\HSL;

HSL::getChannelDefaultValue('h');  // 0
HSL::getChannelDefaultValue('s');  // 0
HSL::getChannelDefaultValue('l');  // 0
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\HSL;

HSL::getChannels();  // ['h', 's', 'l']
```

## Validation

HSL automatically validates channel values:

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::hsl(400, 50, 40);  // Hue exceeds 360
} catch (InvalidColorValueException $e) {
    // Handle error
}

try {
    // This will also throw InvalidColorValueException
    $color = Color::hsl(210, 150, 40);  // Saturation exceeds 100
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Understanding HSL

### Hue
Hue represents the color itself. It's a circular scale:
- **0°/360°**: Red
- **60°**: Yellow
- **120°**: Green
- **180°**: Cyan
- **240°**: Blue
- **300°**: Magenta

### Saturation
Saturation controls how "colorful" the color is:
- **0%**: Completely desaturated (gray)
- **50%**: Moderately colorful
- **100%**: Fully saturated (pure color)

### Lightness
Lightness controls how bright or dark the color is:
- **0%**: Pure black
- **25%**: Dark color
- **50%**: Full color intensity
- **75%**: Light color
- **100%**: Pure white

## Use Cases

- **Color Manipulation**: Easy to create lighter/darker variations
- **Color Harmonies**: Simple to create complementary or analogous colors
- **UI Design**: Intuitive color adjustments for themes
- **Color Pickers**: Most color pickers use HSL or HSV
- **Accessibility**: Easy to adjust lightness for contrast

## Color Space Information

```php
use Negarity\Color\ColorSpace\HSL;

HSL::getName();  // "hsl"
HSL::getChannels();  // ['h', 's', 'l']
HSL::supportsIlluminant();  // false
HSL::supportsObserver();  // false
```

## See Also

- [HSLA Color Space](/docs/references/color-spaces/hsla) - HSL with transparency
- [HSV Color Space](/docs/references/color-spaces/hsv) - Similar to HSL but uses Value
- [Creating Colors](/docs/basics/creating-colors) - How to create HSL colors
- [Getting Channels](/docs/basics/getting-channels) - How to access HSL channel values

---
title: HSV Color Space
sidebar_position: 5
---

# HSV Color Space

**HSV** (Hue, Saturation, Value) is similar to HSL but uses "Value" instead of "Lightness". It's commonly used in color pickers and graphics software because it more closely matches how artists think about color.

## Channels

HSV has three channels:

- **`h`** (Hue): The color itself, represented as an angle
- **`s`** (Saturation): The intensity or purity of the color
- **`v`** (Value): The brightness of the color

## Channel Ranges

- **Hue (`h`)**: 0-360 degrees
  - `0°` = Red
  - `120°` = Green
  - `240°` = Blue
  - `360°` = Red (wraps around)
- **Saturation (`s`)**: 0-100%
  - `0%` = Gray (no color)
  - `100%` = Full color intensity
- **Value (`v`)**: 0-100%
  - `0%` = Black
  - `100%` = Full brightness

## Default Values

All channels default to `0` when not specified, resulting in black (`hsv(0, 0, 0)`).

## Creating HSV Colors

```php
use Negarity\Color\Color;

// Create an HSV color
$color = Color::hsv(210, 50, 40);

// Pure red at full brightness
$red = Color::hsv(0, 100, 100);

// Gray (no saturation)
$gray = Color::hsv(0, 0, 50);
```

## Working with HSV Channels

### Getting Channel Values

```php
$color = Color::hsv(210, 75, 60);

$h = $color->getH();  // 210
$s = $color->getS();  // 75
$v = $color->getV();  // 60
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\HSV;

HSV::hasChannel('h');  // true
HSV::hasChannel('s');  // true
HSV::hasChannel('v');  // true
HSV::hasChannel('l');  // false (that's HSL)
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\HSV;

HSV::getChannelDefaultValue('h');  // 0
HSV::getChannelDefaultValue('s');  // 0
HSV::getChannelDefaultValue('v');  // 0
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\HSV;

HSV::getChannels();  // ['h', 's', 'v']
```

## Validation

HSV automatically validates channel values:

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::hsv(400, 50, 40);  // Hue exceeds 360
} catch (InvalidColorValueException $e) {
    // Handle error
}

try {
    // This will also throw InvalidColorValueException
    $color = Color::hsv(210, 150, 40);  // Saturation exceeds 100
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Understanding HSV

### Hue
Same as HSL - represents the color itself on a circular scale.

### Saturation
Same as HSL - controls how "colorful" the color is.

### Value vs Lightness
The key difference between HSV and HSL is the third channel:

- **HSV Value**: Represents the brightness of the color
  - `0%` = Black (regardless of hue/saturation)
  - `100%` = Full brightness of the color
- **HSL Lightness**: Represents how much white or black is mixed in
  - `0%` = Black
  - `50%` = Pure color
  - `100%` = White

In HSV, changing Value affects brightness while maintaining the color's intensity. In HSL, changing Lightness mixes in white or black.

## Use Cases

- **Color Pickers**: Most graphics software uses HSV
- **Image Editing**: HSV is intuitive for brightness adjustments
- **Artistic Tools**: Matches how artists think about color
- **Color Manipulation**: Easy to adjust brightness without affecting saturation

## Color Space Information

```php
use Negarity\Color\ColorSpace\HSV;

HSV::getName();  // "hsv"
HSV::getChannels();  // ['h', 's', 'v']
HSV::supportsIlluminant();  // false
HSV::supportsObserver();  // false
```

## See Also

- [HSL Color Space](/docs/references/color-spaces/hsl) - Similar but uses Lightness
- [Creating Colors](/docs/basics/creating-colors) - How to create HSV colors
- [Getting Channels](/docs/basics/getting-channels) - How to access HSV channel values

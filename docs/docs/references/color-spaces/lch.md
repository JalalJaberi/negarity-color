---
title: LCh Color Space
sidebar_position: 8
---

# LCh Color Space

**LCh** (Lightness, Chroma, Hue) is a polar representation of Lab color space. It's particularly useful for perceptual color manipulation and creating color harmonies, as it separates lightness, color intensity, and color direction.

## Channels

LCh has three channels:

- **`l`** (Lightness): Perceptual lightness, 0-100
- **`c`** (Chroma): Color intensity or saturation, 0-100+
- **`h`** (Hue): Color direction, 0-360 degrees

## Channel Ranges

- **Lightness (`l`)**: 0-100
  - `0` = Black
  - `50` = Middle gray
  - `100` = White
- **Chroma (`c`)**: 0-100+ (theoretical maximum depends on lightness and hue)
  - `0` = No color (gray)
  - Higher values = More intense color
- **Hue (`h`)**: 0-360 degrees
  - `0°` = Red
  - `90°` = Yellow
  - `180°` = Green
  - `270°` = Blue
  - `360°` = Red (wraps around)

## Default Values

All channels default to `0` when not specified, resulting in black (`lch(0, 0, 0)`).

## CIE Standard Support

LCh supports CIE Standard Illuminants and Observers, just like Lab:

- **Illuminants**: A, B, C, D50, D55, D65, D75, E, F1-F12
- **Observers**: 2° (1931) and 10° (1964)

The default is D65 illuminant with 2° observer.

## Creating LCh Colors

```php
use Negarity\Color\Color;

// Create an LCh color with default illuminant/observer
$color = Color::lch(50, 30, 210);

// Create with specific illuminant and observer
$color = Color::lch(50, 30, 210,
    CIEIlluminant::D50,
    CIEObserver::TenDegree
);
```

## Working with LCh Channels

### Getting Channel Values

```php
$color = Color::lch(75, 45, 240);

$l = $color->getL();  // 75
$c = $color->getC();  // 45
$h = $color->getH();  // 240
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\LCh;

LCh::hasChannel('l');  // true
LCh::hasChannel('c');  // true
LCh::hasChannel('h');  // true
LCh::hasChannel('a');  // false (that's Lab)
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\LCh;

LCh::getChannelDefaultValue('l');  // 0
LCh::getChannelDefaultValue('c');  // 0
LCh::getChannelDefaultValue('h');  // 0
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\LCh;

LCh::getChannels();  // ['l', 'c', 'h']
```

### Checking CIE Support

```php
use Negarity\Color\ColorSpace\LCh;

LCh::supportsIlluminant();  // true
LCh::supportsObserver();  // true
```

## Validation

LCh validates channel values:

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::lch(150, 30, 210);  // Lightness exceeds 100
} catch (InvalidColorValueException $e) {
    // Handle error
}

try {
    // This will also throw InvalidColorValueException
    $color = Color::lch(50, 30, 400);  // Hue exceeds 360
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Understanding LCh

### Relationship to Lab
LCh is a polar (cylindrical) representation of Lab:
- **L** (Lightness): Same as Lab's L channel
- **C** (Chroma): Distance from the neutral axis (calculated from a and b)
- **H** (Hue): Angle around the neutral axis (calculated from a and b)

The conversion is:
- `C = sqrt(a² + b²)`
- `H = atan2(b, a)`

### Advantages of LCh
1. **Intuitive Manipulation**: Easy to adjust lightness, saturation (chroma), and hue independently
2. **Color Harmonies**: Simple to create complementary, triadic, or analogous colors
3. **Perceptual Uniformity**: Inherits Lab's perceptual uniformity
4. **Separation of Concerns**: Lightness, color intensity, and color direction are clearly separated

### Chroma vs Saturation
- **Chroma**: Absolute color intensity (distance from gray)
- **Saturation**: Relative color intensity (chroma relative to lightness)

In LCh, chroma is absolute, making it easier to work with than HSL's saturation.

## Use Cases

- **Color Harmonies**: Creating complementary, triadic, or analogous color schemes
- **Perceptual Color Manipulation**: Adjusting colors in a perceptually uniform way
- **Color Design**: Intuitive color adjustments for design work
- **Color Analysis**: Analyzing color relationships and differences
- **Accessibility**: Adjusting colors while maintaining perceptual relationships

## Color Space Information

```php
use Negarity\Color\ColorSpace\LCh;

LCh::getName();  // "lch"
LCh::getChannels();  // ['l', 'c', 'h']
LCh::supportsIlluminant();  // true
LCh::supportsObserver();  // true
```

## See Also

- [Lab Color Space](/docs/references/color-spaces/lab) - Rectangular representation
- [Creating Colors](/docs/basics/creating-colors) - How to create LCh colors
- [Getting Channels](/docs/basics/getting-channels) - How to access LCh channel values

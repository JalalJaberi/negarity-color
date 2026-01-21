---
title: Lab (CIELAB) Color Space
sidebar_position: 7
---

# Lab (CIELAB) Color Space

**Lab** (also known as CIELAB or L\*a\*b\*) is a perceptually uniform color space designed to approximate human vision. It's widely used in color matching, color analysis, and applications where perceptual uniformity is important.

## Channels

Lab has three channels:

- **`l`** (Lightness): Perceptual lightness, 0-100
- **`a`** (Green-Red axis): Green (negative) to Red (positive)
- **`b`** (Blue-Yellow axis): Blue (negative) to Yellow (positive)

## Channel Ranges

- **Lightness (`l`)**: 0-100
  - `0` = Black
  - `50` = Middle gray
  - `100` = White
- **a channel**: Typically -128 to 127 (theoretical range is wider)
  - Negative values = Green
  - Positive values = Red
  - `0` = Neutral (neither green nor red)
- **b channel**: Typically -128 to 127 (theoretical range is wider)
  - Negative values = Blue
  - Positive values = Yellow
  - `0` = Neutral (neither blue nor yellow)

## Default Values

All channels default to `0` when not specified, resulting in middle gray (`lab(0, 0, 0)`).

## CIE Standard Support

Lab supports CIE Standard Illuminants and Observers, which are essential for accurate color representation:

- **Illuminants**: A, B, C, D50, D55, D65, D75, E, F1-F12
- **Observers**: 2° (1931) and 10° (1964)

The default is D65 illuminant with 2° observer, which is standard for most applications.

## Creating Lab Colors

```php
use Negarity\Color\Color;

// Create a Lab color with default illuminant/observer
$color = Color::lab(50, 20, -30);

// Create with specific illuminant and observer
$color = Color::lab(50, 20, -30, 
    CIEIlluminant::D50, 
    CIEObserver::TenDegree
);
```

## Working with Lab Channels

### Getting Channel Values

```php
$color = Color::lab(75, 25, -40);

$l = $color->getL();  // 75
$a = $color->getA();  // 25
$b = $color->getB();  // -40
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\Lab;

Lab::hasChannel('l');  // true
Lab::hasChannel('a');  // true
Lab::hasChannel('b');  // true
Lab::hasChannel('c');  // false (that's LCh)
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\Lab;

Lab::getChannelDefaultValue('l');  // 0
Lab::getChannelDefaultValue('a');  // 0
Lab::getChannelDefaultValue('b');  // 0
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\Lab;

Lab::getChannels();  // ['l', 'a', 'b']
```

### Checking CIE Support

```php
use Negarity\Color\ColorSpace\Lab;

Lab::supportsIlluminant();  // true
Lab::supportsObserver();  // true
```

## Validation

Lab validates channel values:

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::lab(150, 20, -30);  // Lightness exceeds 100
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Understanding Lab

### Perceptual Uniformity
Lab is **perceptually uniform**, meaning:
- Equal changes in Lab values produce equal perceived color differences
- This makes it ideal for color matching and color difference calculations
- The distance between two colors in Lab space (ΔE) correlates with perceived difference

### The a and b Axes
- **a axis**: Represents the green-red dimension
  - Negative = Green
  - Positive = Red
- **b axis**: Represents the blue-yellow dimension
  - Negative = Blue
  - Positive = Yellow

### Lightness
The L channel represents perceptual lightness, which is more accurate than RGB brightness or HSL lightness for human perception.

## Use Cases

- **Color Matching**: Matching colors across different devices
- **Color Analysis**: Analyzing color differences and similarities
- **Quality Control**: Ensuring color consistency in manufacturing
- **Color Science**: Research and color management
- **Image Processing**: Perceptually uniform color adjustments

## Color Space Information

```php
use Negarity\Color\ColorSpace\Lab;

Lab::getName();  // "lab"
Lab::getChannels();  // ['l', 'a', 'b']
Lab::supportsIlluminant();  // true
Lab::supportsObserver();  // true
```

## See Also

- [LCh Color Space](/docs/references/color-spaces/lch) - Polar representation of Lab
- [XYZ Color Space](/docs/references/color-spaces/xyz) - Device-independent color space
- [Creating Colors](/docs/basics/creating-colors) - How to create Lab colors
- [Getting Channels](/docs/basics/getting-channels) - How to access Lab channel values

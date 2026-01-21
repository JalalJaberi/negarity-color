---
title: XYZ (CIE XYZ) Color Space
sidebar_position: 9
---

# XYZ (CIE XYZ) Color Space

**XYZ** (CIE XYZ) is a device-independent color space based on human color perception. It serves as a fundamental color space in color science and is often used as an intermediate step in color conversions.

## Channels

XYZ has three channels:

- **`x`**: X tristimulus value
- **`y`**: Y tristimulus value (also represents luminance)
- **`z`**: Z tristimulus value

## Channel Ranges

XYZ channels don't have fixed ranges like other color spaces. The values depend on the illuminant and observer used. In Negarity Color:
- Values are typically in the range 0-100 (scaled for convenience)
- The Y channel represents luminance and is often used as a reference

## Default Values

All channels default to `0` when not specified, resulting in black (`xyz(0, 0, 0)`).

## CIE Standard Support

XYZ supports CIE Standard Illuminants and Observers, which are essential for accurate color representation:

- **Illuminants**: A, B, C, D50, D55, D65, D75, E, F1-F12
- **Observers**: 2° (1931) and 10° (1964)

The default is D65 illuminant with 2° observer, which is standard for most applications.

## Creating XYZ Colors

```php
use Negarity\Color\Color;

// Create an XYZ color with default illuminant/observer
$color = Color::xyz(20, 30, 40);

// Create with specific illuminant and observer
$color = Color::xyz(20, 30, 40,
    CIEIlluminant::D50,
    CIEObserver::TenDegree
);
```

## Working with XYZ Channels

### Getting Channel Values

```php
$color = Color::xyz(25, 35, 45);

$x = $color->getX();  // 25
$y = $color->getY();  // 35
$z = $color->getZ();  // 45
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\XYZ;

XYZ::hasChannel('x');  // true
XYZ::hasChannel('y');  // true
XYZ::hasChannel('z');  // true
XYZ::hasChannel('l');  // false (that's Lab)
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\XYZ;

XYZ::getChannelDefaultValue('x');  // 0
XYZ::getChannelDefaultValue('y');  // 0
XYZ::getChannelDefaultValue('z');  // 0
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\XYZ;

XYZ::getChannels();  // ['x', 'y', 'z']
```

### Checking CIE Support

```php
use Negarity\Color\ColorSpace\XYZ;

XYZ::supportsIlluminant();  // true
XYZ::supportsObserver();  // true
```

## Validation

XYZ validates that values are numeric but doesn't enforce strict ranges (as values depend on illuminant/observer):

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::xyz('invalid', 30, 40);  // Non-numeric value
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Understanding XYZ

### Device Independence
XYZ is **device-independent**, meaning:
- It represents colors as humans perceive them, not as devices display them
- It's based on the CIE 1931 color matching functions
- It's not tied to any specific display or printing technology

### The Y Channel
The Y channel is special:
- It represents **luminance** (brightness)
- It's normalized so that a perfect white reflector has Y = 100 (for D65)
- It's used as a reference in many color calculations

### Role in Conversions
XYZ often serves as an intermediate color space:
- Many color conversions go through XYZ
- RGB → XYZ → Lab
- Lab → XYZ → RGB
- This ensures accurate conversions between different color spaces

### Tristimulus Values
X, Y, and Z are called "tristimulus values" because they represent the three types of color receptors in the human eye (cones).

## Use Cases

- **Color Science**: Research and color theory
- **Color Conversions**: Intermediate step in color space conversions
- **Color Management**: Color profiles and color matching
- **Color Calibration**: Device calibration and profiling
- **Color Analysis**: Scientific color measurements

## Color Space Information

```php
use Negarity\Color\ColorSpace\XYZ;

XYZ::getName();  // "xyz"
XYZ::getChannels();  // ['x', 'y', 'z']
XYZ::supportsIlluminant();  // true
XYZ::supportsObserver();  // true
```

## See Also

- [Lab Color Space](/docs/references/color-spaces/lab) - Derived from XYZ
- [LCh Color Space](/docs/references/color-spaces/lch) - Derived from Lab (and thus XYZ)
- [Creating Colors](/docs/basics/creating-colors) - How to create XYZ colors
- [Getting Channels](/docs/basics/getting-channels) - How to access XYZ channel values

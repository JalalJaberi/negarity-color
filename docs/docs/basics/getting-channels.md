---
title: Getting Channels
sidebar_position: 3
---

# Getting Channels

Every color in Negarity Color consists of channels that represent the color's values in its color space. This guide shows you how to access these channel values.

## Clamping and Value Modes

Negarity Color supports two modes for handling channel values:

- **Non-Strict Mode (Default)**: Original values are preserved internally, but clamped values are returned when accessing channels. This allows out-of-gamut colors to exist for advanced operations.
- **Strict Mode**: Values are clamped immediately when assigned. This ensures all stored values are always within valid ranges.

You can access both clamped and raw (original) values using different methods.

## Using getChannel()

The `getChannel()` method allows you to access any channel by name. **This method always returns clamped values** (values within the valid range for that channel):

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);

echo $color->getChannel('r'); // 255
echo $color->getChannel('g'); // 100
echo $color->getChannel('b'); // 50
```

### Getting Raw (Original) Values

In non-strict mode, you can access the original (unclamped) values using `getChannelRaw()`:

```php
// In non-strict mode, out-of-range values are allowed
$color = Color::rgb(300, -10, 50);

echo $color->getChannel('r');    // 255 (clamped)
echo $color->getChannelRaw('r'); // 300 (original)

echo $color->getChannel('g');    // 0 (clamped)
echo $color->getChannelRaw('g'); // -10 (original)
```

This is useful when you need to work with out-of-gamut colors or preserve precision in calculations.

This works for any color space:

```php
$hsl = Color::hsl(210, 50, 40);

echo $hsl->getChannel('h'); // 210
echo $hsl->getChannel('s'); // 50
echo $hsl->getChannel('l'); // 40
```

## Using Getter Methods

For convenience, you can use dedicated getter methods for each channel. These methods are dynamically available based on the color space. **These methods return clamped values** (same as `getChannel()`).

You can also access raw values using `get{Channel}Raw()` methods:

### RGB/RGBA Channels

```php
$color = Color::rgb(255, 100, 50);

echo $color->getR(); // 255 (clamped)
echo $color->getG(); // 100 (clamped)
echo $color->getB(); // 50 (clamped)

// Access raw values
echo $color->getRRaw(); // Original value (may be out of range in non-strict mode)

// For RGBA
$rgba = Color::rgba(255, 100, 50, 128);
echo $rgba->getA(); // 128 (clamped)
echo $rgba->getARaw(); // Original alpha value
```

### HSL/HSLA Channels

```php
$hsl = Color::hsl(210, 50, 40);

echo $hsl->getH(); // 210 (Hue)
echo $hsl->getS(); // 50 (Saturation)
echo $hsl->getL(); // 40 (Lightness)
```

### HSV Channels

```php
$hsv = Color::hsv(210, 50, 40);

echo $hsv->getH(); // 210
echo $hsv->getS(); // 50
echo $hsv->getV(); // 40 (Value)
```

### CMYK Channels

```php
$cmyk = Color::cmyk(0, 50, 100, 0);

echo $cmyk->getC(); // 0 (Cyan)
echo $cmyk->getM(); // 50 (Magenta)
echo $cmyk->getY(); // 100 (Yellow)
echo $cmyk->getK(); // 0 (Key/Black)
```

### Lab Channels

```php
$lab = Color::lab(50, 20, -30);

echo $lab->getL(); // 50 (Lightness)
echo $lab->getA(); // 20
echo $lab->getB(); // -30
```

### LCh Channels

```php
$lch = Color::lch(50, 30, 210);

echo $lch->getL(); // 50 (Lightness)
echo $lch->getC(); // 30 (Chroma)
echo $lch->getH(); // 210 (Hue)
```

### XYZ Channels

```php
$xyz = Color::xyz(20, 30, 40);

echo $xyz->getX(); // 20
echo $xyz->getY(); // 30
echo $xyz->getZ(); // 40
```

### YCbCr Channels

```php
$ycbcr = Color::ycbcr(78, 100, -100);

echo $ycbcr->getY(); // 78
echo $ycbcr->getCb(); // 100
echo $ycbcr->getCr(); // -100
```

## Getting All Channels

### Using getChannels()

Get all channel names for the current color space:

```php
$color = Color::rgb(255, 100, 50);
$channels = $color->getChannels();
// Returns: ['r', 'g', 'b']
```

### Using toArray()

Get all channel values as an associative array:

```php
$color = Color::rgb(255, 100, 50);
$array = $color->toArray();
// Returns: [
//     'color-space' => 'rgb',
//     'values' => ['r' => 255, 'g' => 100, 'b' => 50]
// ]
```

### JSON Serialization

Colors implement `JsonSerializable`, so you can easily serialize them:

```php
$color = Color::rgb(255, 100, 50);
$json = json_encode($color);
// Returns: {"color-space":"rgb","values":{"r":255,"g":100,"b":50}}
```

## Getting Color Space Information

### Color Space Name

Get the human-readable name of the color space:

```php
$color = Color::rgb(255, 100, 50);
echo $color->getColorSpaceName(); // "rgb"
```

### Color Space Class

Get the class name of the color space:

```php
$color = Color::rgb(255, 100, 50);
echo $color->getColorSpace(); // "Negarity\Color\ColorSpace\RGB"
```

## Practical Examples

### Iterating Over Channels

```php
$color = Color::rgb(255, 100, 50);

foreach ($color->getChannels() as $channel) {
    echo "$channel: " . $color->getChannel($channel) . "\n";
}
// Output:
// r: 255
// g: 100
// b: 50
```

### Accessing Channels After Conversion

```php
$rgb = Color::rgb(255, 100, 50);
$hsl = $rgb->toHSL();

// Access HSL channels
echo "Hue: " . $hsl->getH() . "°\n";
echo "Saturation: " . $hsl->getS() . "%\n";
echo "Lightness: " . $hsl->getL() . "%\n";
```

### Working with Alpha Channels

```php
$rgba = Color::rgba(255, 100, 50, 128);

// Check if color has transparency
if ($rgba->getA() < 255) {
    $opacity = ($rgba->getA() / 255) * 100;
    echo "Color is " . $opacity . "% opaque\n";
}
```

## Clamping Behavior

### Non-Strict Mode (Default)

In non-strict mode, out-of-range values are allowed when creating colors:

```php
// This works in non-strict mode (default)
$color = Color::rgb(300, -10, 50);

// getChannel() returns clamped values
echo $color->getR(); // 255 (clamped from 300)
echo $color->getG(); // 0 (clamped from -10)

// getChannelRaw() returns original values
echo $color->getRRaw(); // 300
echo $color->getGRaw(); // -10

// String representation uses clamped values
echo $color; // "rgb(255, 0, 50)"
```

### Strict Mode

In strict mode, values are clamped immediately when assigned. To enable strict mode, you need to override the `STRICT_CLAMPING` constant in a custom class (this is an advanced feature).

## Error Handling

If you try to access a channel that doesn't exist in the current color space, an exception will be thrown:

```php
$rgb = Color::rgb(255, 100, 50);

try {
    echo $rgb->getChannel('h'); // Throws InvalidArgumentException
} catch (\InvalidArgumentException $e) {
    echo "Channel 'h' doesn't exist in RGB color space\n";
}
```

## Next Steps

Now that you can access channel values, learn about:
- [Modifying colors](/docs/basics/modifying-colors)
- [Converting between color spaces](/docs/basics/converting-colors)

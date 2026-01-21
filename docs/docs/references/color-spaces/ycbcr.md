---
title: YCbCr Color Space
sidebar_position: 10
---

# YCbCr Color Space

**YCbCr** separates luminance (brightness) from chrominance (color information). It's widely used in video encoding, digital photography, and image compression formats like JPEG.

## Channels

YCbCr has three channels:

- **`y`** (Luminance): Brightness information, 0.0-100.0
- **`cb`** (Blue-difference chroma): Blue-yellow color information, -128 to 127
- **`cr`** (Red-difference chroma): Red-green color information, -128 to 127

## Channel Ranges

- **Luminance (`y`)**: 0.0-100.0 (float)
  - `0.0` = Black
  - `100.0` = White
- **Cb (Blue-difference)**: -128 to 127 (integer)
  - Negative = More blue
  - Positive = More yellow
  - `0` = Neutral
- **Cr (Red-difference)**: -128 to 127 (integer)
  - Negative = More green
  - Positive = More red
  - `0` = Neutral

## Default Values

All channels default to `0` when not specified, resulting in black (`ycbcr(0, 0, 0)`).

## Creating YCbCr Colors

```php
use Negarity\Color\Color;

// Create a YCbCr color
$color = Color::ycbcr(78.5, 100, -100);

// Note: Y is a float, Cb and Cr are integers
$color = Color::ycbcr(50.0, 0, 0);  // Gray
```

## Working with YCbCr Channels

### Getting Channel Values

```php
$color = Color::ycbcr(78.5, 50, -75);

$y = $color->getY();   // 78.5 (float)
$cb = $color->getCb(); // 50 (int)
$cr = $color->getCr(); // -75 (int)
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\YCbCr;

YCbCr::hasChannel('y');  // true
YCbCr::hasChannel('cb'); // true
YCbCr::hasChannel('cr'); // true
YCbCr::hasChannel('l');  // false (that's Lab/LCh)
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\YCbCr;

YCbCr::getChannelDefaultValue('y');  // 0
YCbCr::getChannelDefaultValue('cb'); // 0
YCbCr::getChannelDefaultValue('cr'); // 0
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\YCbCr;

YCbCr::getChannels();  // ['y', 'cb', 'cr']
```

## Validation

YCbCr validates channel values with different ranges for each channel:

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::ycbcr(150.0, 50, -75);  // Y exceeds 100.0
} catch (InvalidColorValueException $e) {
    // Handle error
}

try {
    // This will also throw InvalidColorValueException
    $color = Color::ycbcr(50.0, 200, -75);  // Cb exceeds 127
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Understanding YCbCr

### Luminance-Chrominance Separation
YCbCr separates an image into:
- **Y (Luminance)**: The brightness/grayscale information
- **Cb and Cr (Chrominance)**: The color information

This separation is useful because:
- Human vision is more sensitive to brightness than color
- Chrominance can be compressed more aggressively
- Allows for efficient image and video compression

### The Cb and Cr Channels
- **Cb (Blue-difference)**: Represents the blue-yellow axis
  - Negative values = More blue
  - Positive values = More yellow
- **Cr (Red-difference)**: Represents the red-green axis
  - Negative values = More green
  - Positive values = More red

### Mixed Data Types
YCbCr is unique in that it uses:
- **Float for Y**: Allows for precise luminance values
- **Integer for Cb/Cr**: Sufficient precision for chrominance

## Use Cases

- **Video Encoding**: MPEG, H.264, H.265 video formats
- **Image Compression**: JPEG uses a variant of YCbCr
- **Digital Photography**: Many cameras use YCbCr internally
- **Broadcast Television**: Standard for TV signal transmission
- **Image Processing**: Efficient color manipulation and compression

## Color Space Information

```php
use Negarity\Color\ColorSpace\YCbCr;

YCbCr::getName();  // "ycbcr"
YCbCr::getChannels();  // ['y', 'cb', 'cr']
YCbCr::supportsIlluminant();  // false
YCbCr::supportsObserver();  // false
```

## See Also

- [Creating Colors](/docs/basics/creating-colors) - How to create YCbCr colors
- [Getting Channels](/docs/basics/getting-channels) - How to access YCbCr channel values

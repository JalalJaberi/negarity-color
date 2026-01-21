---
title: Color Spaces Reference
sidebar_position: 0
---

# Color Spaces Reference

A **color space** is a specific organization of colors that defines how colors are represented numerically. Different color spaces are optimized for different purposes—some are intuitive for human perception, others are optimized for displays, printing, or color science.

## What is a Color Space?

In Negarity Color, a color space is defined by the `ColorSpaceInterface`, which provides a standardized way to work with colors:

- **Channels**: Each color space has specific channels (e.g., RGB has `r`, `g`, `b`)
- **Ranges**: Each channel has a valid range of values
- **Validation**: Values are automatically validated to ensure they're within acceptable ranges
- **Default Values**: Each channel has a default value when not specified

All color spaces in Negarity Color extend `AbstractColorSpace`, which provides common functionality like:
- Channel validation
- Range checking
- Support for CIE Standard Illuminants and Observers (for certain color spaces)

## Available Color Spaces

Negarity Color supports the following color spaces:

### Screen & Web Color Spaces

- **[RGB](/docs/references/color-spaces/rgb)** - Red, Green, Blue - The standard color space for digital displays
- **[RGBA](/docs/references/color-spaces/rgba)** - RGB with Alpha channel for transparency

### Perceptual Color Spaces

- **[HSL](/docs/references/color-spaces/hsl)** - Hue, Saturation, Lightness - Intuitive for color manipulation
- **[HSLA](/docs/references/color-spaces/hsla)** - HSL with Alpha channel
- **[HSV](/docs/references/color-spaces/hsv)** - Hue, Saturation, Value - Common in color pickers

### Print Color Spaces

- **[CMYK](/docs/references/color-spaces/cmyk)** - Cyan, Magenta, Yellow, Key (Black) - Used for professional printing

### Color Science Spaces

- **[Lab (CIELAB)](/docs/references/color-spaces/lab)** - Perceptually uniform color space for color matching
- **[LCh](/docs/references/color-spaces/lch)** - Lightness, Chroma, Hue - Polar representation of Lab
- **[XYZ (CIE XYZ)](/docs/references/color-spaces/xyz)** - Device-independent color space for color science

### Video & Compression

- **[YCbCr](/docs/references/color-spaces/ycbcr)** - Luminance and chrominance separation for video encoding

## Color Space Interface Methods

All color spaces implement the `ColorSpaceInterface`, which provides these methods:

### `getName(): string`
Returns the unique name of the color space (e.g., `"rgb"`, `"hsl"`, `"lab"`).

### `getChannels(): array`
Returns all channel names in order (e.g., `["r", "g", "b"]` for RGB).

### `hasChannel(string $name): bool`
Checks if a channel exists by name.

### `getChannelDefaultValue(string $name): float|int`
Returns the default value for a channel when not specified.

### `validateValue(string $channel, int|float $value): void`
Validates that a channel value is within the acceptable range. Throws `InvalidColorValueException` if invalid.

## CIE Standard Support

Some color spaces support CIE Standard Illuminants and Observers:

- **Lab** - Supports illuminants and observers
- **LCh** - Supports illuminants and observers  
- **XYZ** - Supports illuminants and observers

You can check if a color space supports these features using:
- `supportsIlluminant(): bool`
- `supportsObserver(): bool`

## Choosing a Color Space

- **RGB/RGBA**: For screen/web applications, digital images
- **HSL/HSV**: For intuitive color manipulation and adjustments
- **CMYK**: For print media and professional printing
- **Lab/LCh**: For color matching, analysis, and perceptually uniform operations
- **XYZ**: For color science and as an intermediate in conversions
- **YCbCr**: For video encoding, digital photography, and JPEG compression

## See Also

- [Creating Colors](/docs/basics/creating-colors) - How to create colors in different spaces
- [Converting Colors](/docs/basics/converting-colors) - How to convert between spaces
- [Getting Channels](/docs/basics/getting-channels) - How to access color channel values

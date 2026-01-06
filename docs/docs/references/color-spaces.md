---
title: Color Spaces Reference
sidebar_position: 1
---

# Color Spaces Reference

This is a complete reference for all color spaces supported by Negarity Color.

## RGB (Red, Green, Blue)

**Channels**: `r`, `g`, `b`  
**Range**: 0-255 for each channel  
**Use Case**: Screen display, web colors, digital images

```php
$color = Color::rgb(255, 100, 50);
```

RGB is the most common color space for digital displays. Each channel represents the intensity of red, green, or blue light.

## RGBA (Red, Green, Blue, Alpha)

**Channels**: `r`, `g`, `b`, `a`  
**Range**: 0-255 for each channel  
**Use Case**: Colors with transparency, web graphics

```php
$color = Color::rgba(255, 100, 50, 128); // 50% opacity
```

RGBA extends RGB with an alpha channel for transparency. Alpha 0 is fully transparent, 255 is fully opaque.

## HSL (Hue, Saturation, Lightness)

**Channels**: `h`, `s`, `l`  
**Range**: H: 0-360°, S: 0-100%, L: 0-100%  
**Use Case**: Color manipulation, intuitive color adjustments

```php
$color = Color::hsl(210, 50, 40);
```

HSL is intuitive for color manipulation:
- **Hue**: The color itself (0°=red, 120°=green, 240°=blue)
- **Saturation**: Color intensity (0%=gray, 100%=full color)
- **Lightness**: Brightness (0%=black, 100%=white)

## HSLA (Hue, Saturation, Lightness, Alpha)

**Channels**: `h`, `s`, `l`, `a`  
**Range**: H: 0-360°, S: 0-100%, L: 0-100%, A: 0-255  
**Use Case**: HSL colors with transparency

```php
$color = Color::hsla(210, 50, 40, 200);
```

## HSV (Hue, Saturation, Value)

**Channels**: `h`, `s`, `v`  
**Range**: H: 0-360°, S: 0-100%, V: 0-100%  
**Use Case**: Color pickers, graphics software

```php
$color = Color::hsv(210, 50, 40);
```

Similar to HSL but uses Value instead of Lightness:
- **Value**: Brightness of the color (0%=black, 100%=full brightness)

## CMYK (Cyan, Magenta, Yellow, Key/Black)

**Channels**: `c`, `m`, `y`, `k`  
**Range**: 0-100% for each channel  
**Use Case**: Print media, professional printing

```php
$color = Color::cmyk(0, 50, 100, 0);
```

CMYK is used for printing. Higher values mean more ink. The K (key/black) channel is used for richer blacks and cost efficiency.

## Lab (CIELAB)

**Channels**: `l`, `a`, `b`  
**Range**: L: 0-100, a: -128 to 127, b: -128 to 127  
**Use Case**: Perceptually uniform color, color matching

```php
$color = Color::lab(50, 20, -30);
```

Lab is perceptually uniform, meaning equal changes in values produce equal perceived color differences. Useful for color matching and analysis.

## LCh (Lightness, Chroma, Hue)

**Channels**: `l`, `c`, `h`  
**Range**: L: 0-100, C: 0-100, H: 0-360°  
**Use Case**: Perceptual color manipulation, color harmony

```php
$color = Color::lch(50, 30, 210);
```

LCh is a polar representation of Lab:
- **Lightness**: Same as Lab
- **Chroma**: Color intensity (distance from gray)
- **Hue**: Color direction

## XYZ (CIE XYZ)

**Channels**: `x`, `y`, `z`  
**Range**: Typically 0-100  
**Use Case**: Color science, color space conversions

```php
$color = Color::xyz(20, 30, 40);
```

XYZ is a device-independent color space used as an intermediate in many color conversions. Based on human color perception.

## YCbCr

**Channels**: `y`, `cb`, `cr`  
**Range**: Y: 0-255, Cb: 0-255, Cr: 0-255  
**Use Case**: Video encoding, digital photography, JPEG compression

```php
$color = Color::ycbcr(128, 100, 150);
```

YCbCr separates luminance (Y) from chrominance (Cb, Cr):
- **Y**: Luminance (brightness)
- **Cb**: Blue-difference chroma
- **Cr**: Red-difference chroma

## Conversion Support

All color spaces can be converted to and from each other. The library automatically handles intermediate conversions (usually through RGB or XYZ) to ensure accuracy.

## Choosing a Color Space

- **RGB/RGBA**: For screen/web applications
- **HSL/HSV**: For intuitive color manipulation
- **CMYK**: For print media
- **Lab/LCh**: For color matching and analysis
- **XYZ**: For color science and conversions
- **YCbCr**: For video and image compression

## See Also

- [Creating Colors](/docs/basics/creating-colors) - How to create colors in different spaces
- [Converting Colors](/docs/basics/converting-colors) - How to convert between spaces

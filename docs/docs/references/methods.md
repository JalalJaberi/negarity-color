---
title: Methods Reference
sidebar_position: 2
---

# Methods Reference

Complete reference for all methods available on `Color` and `MutableColor` classes.

## Static Factory Methods

### Color Creation

#### `rgb(int $r, int $g, int $b): static`
Creates a color in RGB color space.

```php
$color = Color::rgb(255, 100, 50);
```

#### `rgba(int $r, int $g, int $b, int $a): static`
Creates a color in RGBA color space with alpha channel.

```php
$color = Color::rgba(255, 100, 50, 128);
```

#### `hsl(int $h, int $s, int $l): static`
Creates a color in HSL color space.

```php
$color = Color::hsl(210, 50, 40);
```

#### `hsla(int $h, int $s, int $l, int $a): static`
Creates a color in HSLA color space.

```php
$color = Color::hsla(210, 50, 40, 200);
```

#### `hsv(int $h, int $s, int $v): static`
Creates a color in HSV color space.

```php
$color = Color::hsv(210, 50, 40);
```

#### `cmyk(int $c, int $m, int $y, int $k): static`
Creates a color in CMYK color space.

```php
$color = Color::cmyk(0, 50, 100, 0);
```

#### `lab(int $l, int $a, int $b): static`
Creates a color in Lab color space.

```php
$color = Color::lab(50, 20, -30);
```

#### `lch(int $l, int $c, int $h): static`
Creates a color in LCh color space.

```php
$color = Color::lch(50, 30, 210);
```

#### `xyz(int $x, int $y, int $z): static`
Creates a color in XYZ color space.

```php
$color = Color::xyz(20, 30, 40);
```

#### `ycbcr(int $y, int $cb, int $cr): static`
Creates a color in YCbCr color space.

```php
$color = Color::ycbcr(128, 100, 150);
```

#### `hex(string $value, string $colorSpace = RGB::class): static`
Creates a color from a hexadecimal string.

```php
$color = Color::hex('#FF6432');
$color = Color::hex('FF6432', HSL::class);
```

Supports formats: `#RGB`, `#RGBA`, `#RRGGBB`, `#RRGGBBAA`

#### `__callStatic(string $name, array $arguments): static`
Creates a color from a named color (requires registered color registry).

```php
Color::addRegistry(new VGANamedColors());
$red = Color::red();
$blue = Color::blue(HSL::class);
```

## Instance Methods

### Color Space Information

#### `getColorSpace(): string`
Returns the color space class name.

```php
$space = $color->getColorSpace(); // "Negarity\Color\ColorSpace\RGB"
```

#### `getColorSpaceName(): string`
Returns the human-readable color space name.

```php
$name = $color->getColorSpaceName(); // "rgb"
```

#### `getChannels(): array`
Returns all channel names for the current color space.

```php
$channels = $color->getChannels(); // ['r', 'g', 'b']
```

### Channel Access

#### `getChannel(string $name): float|int`
Gets a specific channel value by name.

```php
$r = $color->getChannel('r');
```

#### `getR()`, `getG()`, `getB()`, `getA()`, etc.
Dynamic getter methods for each channel.

```php
$r = $color->getR();
$h = $color->getH(); // For HSL/HSV
$c = $color->getC(); // For CMYK/LCh
```

### Conversion Methods

#### `toRGB(): static`
Converts the color to RGB color space.

```php
$rgb = $color->toRGB();
```

#### `toRGBA(int $alpha = 255): static`
Converts the color to RGBA color space.

```php
$rgba = $color->toRGBA();
$rgba = $color->toRGBA(128);
```

#### `toHSL(): static`
Converts the color to HSL color space.

```php
$hsl = $color->toHSL();
```

#### `toHSLA(int $alpha = 255): static`
Converts the color to HSLA color space.

```php
$hsla = $color->toHSLA();
```

#### `toHSV(): static`
Converts the color to HSV color space.

```php
$hsv = $color->toHSV();
```

#### `toCMYK(): static`
Converts the color to CMYK color space.

```php
$cmyk = $color->toCMYK();
```

#### `toLab(): static`
Converts the color to Lab color space.

```php
$lab = $color->toLab();
```

#### `toLCh(): static`
Converts the color to LCh color space.

```php
$lch = $color->toLCh();
```

#### `toXYZ(): static`
Converts the color to XYZ color space.

```php
$xyz = $color->toXYZ();
```

#### `toYCbCr(): static`
Converts the color to YCbCr color space.

```php
$ycbcr = $color->toYCbCr();
```

### Modification Methods

#### `with(array $channels): static`
Creates a new color with specified channel values changed.

```php
$modified = $color->with(['r' => 200, 'g' => 150]);
```

#### `without(array $channels): static`
Creates a new color with specified channels reset to default values.

```php
$noRed = $color->without(['r']);
```

### Utility Methods

#### `toArray(): array`
Returns the color as an associative array.

```php
$array = $color->toArray();
// ['color-space' => 'rgb', 'values' => ['r' => 255, 'g' => 100, 'b' => 50]]
```

#### `toHex(): string`
Converts the color to a hexadecimal string.

```php
$hex = $color->toHex(); // "#FF6432"
```

#### `__toString(): string`
Returns a string representation of the color.

```php
echo $color; // "rgb(255, 100, 50)"
```

#### `jsonSerialize(): array`
Implements `JsonSerializable` for JSON encoding.

```php
$json = json_encode($color);
```

### Filter Methods (Dynamic)

When filters are registered, they become available as methods:

```php
FilterRegistry::register(new BrightnessFilter());
$brighter = $color->brightness(20);

FilterRegistry::register(new InvertFilter());
$inverted = $color->invert();
```

## MutableColor-Specific Methods

#### `setChannel(string $name, float|int $value): void`
Directly sets a channel value (only on `MutableColor`).

```php
$mutable = new MutableColor(RGB::class, ['r' => 255, 'g' => 100, 'b' => 50]);
$mutable->setChannel('r', 200);
```

## Static Registry Methods

#### `addRegistry(NamedColorRegistryInterface $registry): void`
Adds a named color registry.

```php
Color::addRegistry(new VGANamedColors());
```

## See Also

- [Creating Colors](/docs/basics/creating-colors) - Detailed guide on creating colors
- [Converting Colors](/docs/basics/converting-colors) - Guide on conversions
- [Modifying Colors](/docs/basics/modifying-colors) - Guide on modifications

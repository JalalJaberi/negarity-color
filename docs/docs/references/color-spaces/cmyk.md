---
title: CMYK Color Space
sidebar_position: 6
---

# CMYK Color Space

**CMYK** (Cyan, Magenta, Yellow, Key/Black) is a subtractive color model used primarily in professional printing. Unlike RGB which is additive (adding light), CMYK is subtractive (subtracting light by adding ink).

## Channels

CMYK has four channels:

- **`c`** (Cyan): Amount of cyan ink
- **`m`** (Magenta): Amount of magenta ink
- **`y`** (Yellow): Amount of yellow ink
- **`k`** (Key/Black): Amount of black ink

## Channel Ranges

All channels have the same range:
- **Minimum**: 0 (no ink)
- **Maximum**: 100 (full ink coverage)

Values are typically expressed as percentages (0-100%).

## Default Values

All channels default to `0` when not specified, resulting in white (`cmyk(0, 0, 0, 0)`), which represents no ink on white paper.

## Creating CMYK Colors

```php
use Negarity\Color\Color;

// Create a CMYK color
$color = Color::cmyk(0, 50, 100, 0);

// Pure cyan
$cyan = Color::cmyk(100, 0, 0, 0);

// Rich black (using all inks)
$richBlack = Color::cmyk(0, 0, 0, 100);
```

## Working with CMYK Channels

### Getting Channel Values

```php
$color = Color::cmyk(25, 50, 75, 10);

$c = $color->getC();  // 25
$m = $color->getM();  // 50
$y = $color->getY();  // 75
$k = $color->getK();  // 10
```

### Checking Channel Existence

```php
use Negarity\Color\ColorSpace\CMYK;

CMYK::hasChannel('c');  // true
CMYK::hasChannel('m');  // true
CMYK::hasChannel('y');  // true
CMYK::hasChannel('k');  // true
CMYK::hasChannel('r');  // false (that's RGB)
```

### Getting Channel Defaults

```php
use Negarity\Color\ColorSpace\CMYK;

CMYK::getChannelDefaultValue('c');  // 0
CMYK::getChannelDefaultValue('m');  // 0
CMYK::getChannelDefaultValue('y');  // 0
CMYK::getChannelDefaultValue('k');  // 0
```

### Getting All Channels

```php
use Negarity\Color\ColorSpace\CMYK;

CMYK::getChannels();  // ['c', 'm', 'y', 'k']
```

## Validation

CMYK automatically validates channel values:

```php
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;

try {
    // This will throw InvalidColorValueException
    $color = Color::cmyk(150, 50, 100, 0);  // Cyan exceeds 100
} catch (InvalidColorValueException $e) {
    // Handle error
}
```

## Understanding CMYK

### Subtractive Color Model
CMYK is subtractive because:
- **Adding ink** = **Subtracting light**
- More ink = darker color
- Less ink = lighter color
- White = no ink (paper shows through)
- Black = all inks (or just K for efficiency)

### The K Channel (Key/Black)
The black channel serves two purposes:
1. **Richer blacks**: Pure black (K=100) is deeper than mixing CMY
2. **Cost efficiency**: Using black ink is cheaper than mixing cyan, magenta, and yellow

### Color Mixing
- **Cyan + Magenta** = Blue
- **Magenta + Yellow** = Red
- **Yellow + Cyan** = Green
- **Cyan + Magenta + Yellow** = Dark brown/black (but K is preferred)

## Use Cases

- **Professional Printing**: Offset printing, digital printing
- **Print Design**: Preparing designs for print media
- **Brand Colors**: Many brand colors are defined in CMYK
- **Color Matching**: Matching screen colors to printed output
- **Prepress**: Preparing files for commercial printing

## Color Space Information

```php
use Negarity\Color\ColorSpace\CMYK;

CMYK::getName();  // "cmyk"
CMYK::getChannels();  // ['c', 'm', 'y', 'k']
CMYK::supportsIlluminant();  // false
CMYK::supportsObserver();  // false
```

## See Also

- [Creating Colors](/docs/basics/creating-colors) - How to create CMYK colors
- [Getting Channels](/docs/basics/getting-channels) - How to access CMYK channel values

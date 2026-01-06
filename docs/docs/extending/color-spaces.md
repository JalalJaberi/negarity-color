---
title: Adding New Color Spaces
sidebar_position: 1
---

# Adding New Color Spaces

This guide shows you how to extend Negarity Color by adding custom color spaces.

## Overview

To add a new color space, you need to:
1. Create a color space class implementing `ColorSpaceInterface`
2. Add conversion methods to the `Color` class
3. Optionally add factory methods for convenience

## Step 1: Create the Color Space Class

Create a new class extending `AbstractColorSpace`:

```php
<?php

namespace MyApp\ColorSpace;

use Negarity\Color\ColorSpace\AbstractColorSpace;
use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class MyCustomColorSpace extends AbstractColorSpace
{
    public static function getName(): string
    {
        return 'mycustom'; // Lowercase, no spaces
    }

    public static function getChannels(): array
    {
        return ['channel1', 'channel2', 'channel3'];
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['channel1', 'channel2', 'channel3'], true);
    }

    public static function getChannelDefaultValue(string $name): float|int
    {
        return match ($name) {
            'channel1', 'channel2', 'channel3' => 0,
            default => throw new InvalidColorValueException(
                "Channel '{$name}' does not exist in mycustom color space."
            ),
        };
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'channel1', 'channel2', 'channel3' => static::assertRange(
                (int)$value, 
                0, 
                100, 
                $channel
            ),
            default => throw new InvalidColorValueException(
                "Channel '{$channel}' does not exist in mycustom color space."
            ),
        };
    }
}
```

## Step 2: Add Conversion Methods

You need to add conversion methods to both `Color` and `MutableColor` classes. Since these are final classes, you'll need to extend them or modify the library.

### For Color Class

Add conversion methods in the `Color` class:

```php
public function toMyCustom(): static
{
    // Convert from current color space to your custom space
    $rgb = $this->toRGB();
    
    // Your conversion logic here
    $channel1 = /* conversion calculation */;
    $channel2 = /* conversion calculation */;
    $channel3 = /* conversion calculation */;
    
    return new self(MyCustomColorSpace::class, [
        'channel1' => $channel1,
        'channel2' => $channel2,
        'channel3' => $channel3,
    ]);
}
```

### For MutableColor Class

Add similar methods to `MutableColor`:

```php
public function toMyCustom(): static
{
    $rgb = $this->toRGB();
    
    // Conversion logic
    $channel1 = /* calculation */;
    $channel2 = /* calculation */;
    $channel3 = /* calculation */;
    
    $this->colorSpace = MyCustomColorSpace::class;
    $this->values = [
        'channel1' => $channel1,
        'channel2' => $channel2,
        'channel3' => $channel3,
    ];
    
    return $this;
}
```

## Step 3: Add Factory Methods (Optional)

Add static factory methods to `AbstractColor`:

```php
public static function mycustom(int $c1, int $c2, int $c3): static
{
    return new static(MyCustomColorSpace::class, [
        'channel1' => $c1,
        'channel2' => $c2,
        'channel3' => $c3,
    ]);
}
```

## Step 4: Handle Conversions from Your Space

Add conversion FROM your color space to RGB in the `toRGB()` method:

```php
// In Color::toRGB() switch statement
case MyCustomColorSpace::class:
    // Convert from your space to RGB
    $c1 = $this->getChannel('channel1');
    $c2 = $this->getChannel('channel2');
    $c3 = $this->getChannel('channel3');
    
    // Your conversion logic
    $r = /* calculation */;
    $g = /* calculation */;
    $b = /* calculation */;
    
    return self::rgb((int)$r, (int)$g, (int)$b);
```

## Complete Example

Here's a complete example for a hypothetical "XYZ2" color space:

```php
<?php

namespace MyApp\ColorSpace;

use Negarity\Color\ColorSpace\AbstractColorSpace;
use Negarity\Color\Exception\InvalidColorValueException;

final class XYZ2 extends AbstractColorSpace
{
    public static function getName(): string
    {
        return 'xyz2';
    }

    public static function getChannels(): array
    {
        return ['x', 'y', 'z'];
    }

    public static function hasChannel(string $name): bool
    {
        return in_array($name, ['x', 'y', 'z'], true);
    }

    public static function getChannelDefaultValue(string $name): float|int
    {
        return match ($name) {
            'x', 'y', 'z' => 0,
            default => throw new InvalidColorValueException(
                "Channel '{$name}' does not exist in xyz2 color space."
            ),
        };
    }

    public static function validateValue(string $channel, int|float $value): void
    {
        match ($channel) {
            'x', 'y', 'z' => static::assertRange((int)$value, 0, 100, $channel),
            default => throw new InvalidColorValueException(
                "Channel '{$channel}' does not exist in xyz2 color space."
            ),
        };
    }
}
```

## Important Notes

- **Color space names** must be lowercase and unique
- **Channel validation** is important for data integrity
- **Conversion accuracy** depends on your conversion algorithms
- **Default values** should represent "no color" or "black" for your space
- **Range validation** prevents invalid color values

## Testing Your Color Space

```php
use MyApp\ColorSpace\XYZ2;
use Negarity\Color\Color;

// Create color in your space
$color = new Color(XYZ2::class, ['x' => 50, 'y' => 60, 'z' => 70]);

// Convert to RGB
$rgb = $color->toRGB();

// Convert back
$xyz2 = $rgb->toXYZ2();
```

## See Also

- [Color Spaces Reference](/docs/references/color-spaces) - See existing color spaces
- [Converting Colors](/docs/basics/converting-colors) - Understand conversions

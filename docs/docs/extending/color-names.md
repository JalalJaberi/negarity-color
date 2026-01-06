---
title: Adding Color Names
sidebar_position: 2
---

# Adding Color Names

This guide shows you how to create custom color registries with your own named colors.

## Overview

To add custom named colors, you need to:
1. Create a class implementing `NamedColorRegistryInterface`
2. Define your color names and their values in different color spaces
3. Register the registry with `Color::addRegistry()`

## Step 1: Create the Registry Class

Create a class implementing `NamedColorRegistryInterface`:

```php
<?php

namespace MyApp\ColorRegistry;

use Negarity\Color\Registry\NamedColorRegistryInterface;
use Negarity\Color\ColorSpace\ColorSpaceInterface;
use Negarity\Color\ColorSpace\{
    RGB, RGBA, CMYK, HSL, HSLA, HSV, Lab, LCh, XYZ, YCbCr
};

class MyColorRegistry implements NamedColorRegistryInterface
{
    private array $colors = [
        'myred' => [
            RGB::class => ['r' => 200, 'g' => 50, 'b' => 50],
            RGBA::class => ['r' => 200, 'g' => 50, 'b' => 50, 'a' => 255],
            HSL::class => ['h' => 0, 's' => 60, 'l' => 49],
            // Add other color spaces as needed
        ],
        'myblue' => [
            RGB::class => ['r' => 50, 'g' => 100, 'b' => 200],
            RGBA::class => ['r' => 50, 'g' => 100, 'b' => 200, 'a' => 255],
            HSL::class => ['h' => 220, 's' => 60, 'l' => 49],
        ],
        // Add more colors...
    ];

    public function has(string $colorName, string $colorSpace): bool
    {
        $colorName = strtolower($colorName);
        return isset($this->colors[$colorName][$colorSpace]);
    }

    public function getColorValuesByName(string $colorName, string $colorSpace): array
    {
        $colorName = strtolower($colorName);
        
        if (!isset($this->colors[$colorName])) {
            throw new \InvalidArgumentException(
                "Color '{$colorName}' not found in registry."
            );
        }
        
        if (!isset($this->colors[$colorName][$colorSpace])) {
            throw new \InvalidArgumentException(
                "Color '{$colorName}' not available in color space '{$colorSpace}'."
            );
        }
        
        return $this->colors[$colorName][$colorSpace];
    }
}
```

## Step 2: Define Colors for Multiple Spaces

For best compatibility, define each color in multiple color spaces:

```php
private array $colors = [
    'brandprimary' => [
        RGB::class => ['r' => 255, 'g' => 100, 'b' => 50],
        RGBA::class => ['r' => 255, 'g' => 100, 'b' => 50, 'a' => 255],
        CMYK::class => ['c' => 0, 'm' => 61, 'y' => 80, 'k' => 0],
        HSL::class => ['h' => 15, 's' => 100, 'l' => 60],
        HSLA::class => ['h' => 15, 's' => 100, 'l' => 60, 'a' => 255],
        HSV::class => ['h' => 15, 's' => 80, 'v' => 100],
        Lab::class => ['l' => 60, 'a' => 45, 'b' => 65],
        LCh::class => ['l' => 60, 'c' => 78, 'h' => 55],
        XYZ::class => ['x' => 24, 'y' => 18, 'z' => 3],
        YCbCr::class => ['y' => 142, 'cb' => 95, 'cr' => 180],
    ],
];
```

## Step 3: Register the Registry

Register your custom registry:

```php
use Negarity\Color\Color;
use MyApp\ColorRegistry\MyColorRegistry;

Color::addRegistry(new MyColorRegistry());
```

## Step 4: Use Your Named Colors

Once registered, use your named colors:

```php
// Use in default RGB space
$myRed = Color::myred();

// Use in specific color space
$myRedHsl = Color::myred(HSL::class);
$myBlue = Color::myblue();
```

## Complete Example

Here's a complete example for a brand color registry:

```php
<?php

namespace MyApp\ColorRegistry;

use Negarity\Color\Registry\NamedColorRegistryInterface;
use Negarity\Color\ColorSpace\ColorSpaceInterface;
use Negarity\Color\ColorSpace\{
    RGB, RGBA, CMYK, HSL, HSLA, HSV, Lab, LCh, XYZ, YCbCr
};

class BrandColors implements NamedColorRegistryInterface
{
    private array $colors = [
        'brandprimary' => [
            RGB::class => ['r' => 0, 'g' => 102, 'b' => 204],
            RGBA::class => ['r' => 0, 'g' => 102, 'b' => 204, 'a' => 255],
            HSL::class => ['h' => 210, 's' => 100, 'l' => 40],
        ],
        'brandsecondary' => [
            RGB::class => ['r' => 255, 'g' => 153, 'b' => 0],
            RGBA::class => ['r' => 255, 'g' => 153, 'b' => 0, 'a' => 255],
            HSL::class => ['h' => 36, 's' => 100, 'l' => 50],
        ],
        'brandaccent' => [
            RGB::class => ['r' => 204, 'g' => 0, 'b' => 102],
            RGBA::class => ['r' => 204, 'g' => 0, 'b' => 102, 'a' => 255],
            HSL::class => ['h' => 330, 's' => 100, 'l' => 40],
        ],
    ];

    public function has(string $colorName, string $colorSpace): bool
    {
        $colorName = strtolower($colorName);
        return isset($this->colors[$colorName][$colorSpace]);
    }

    public function getColorValuesByName(string $colorName, string $colorSpace): array
    {
        $colorName = strtolower($colorName);
        
        if (!isset($this->colors[$colorName])) {
            throw new \InvalidArgumentException(
                "Color '{$colorName}' not found in BrandColors registry."
            );
        }
        
        if (!isset($this->colors[$colorName][$colorSpace])) {
            throw new \InvalidArgumentException(
                "Color '{$colorName}' not available in color space '{$colorSpace}'."
            );
        }
        
        return $this->colors[$colorName][$colorSpace];
    }
}
```

Usage:

```php
use Negarity\Color\Color;
use MyApp\ColorRegistry\BrandColors;

// Register
Color::addRegistry(new BrandColors());

// Use
$primary = Color::brandprimary();
$secondary = Color::brandsecondary();
$accent = Color::brandaccent();
```

## Tips

- **Color names** are case-insensitive (automatically lowercased)
- **Multiple registries** can be registered - they're searched in order
- **Color space support** - define colors in all spaces you need
- **Consistent naming** - use clear, descriptive names
- **Documentation** - document your color values and their meanings

## Loading from External Sources

You can load colors from files, databases, or APIs:

```php
class DatabaseColorRegistry implements NamedColorRegistryInterface
{
    public function __construct(private PDO $db) {}
    
    public function has(string $colorName, string $colorSpace): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM colors WHERE name = ? AND space = ?"
        );
        $stmt->execute([strtolower($colorName), $colorSpace]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function getColorValuesByName(string $colorName, string $colorSpace): array
    {
        $stmt = $this->db->prepare(
            "SELECT values FROM colors WHERE name = ? AND space = ?"
        );
        $stmt->execute([strtolower($colorName), $colorSpace]);
        $result = $stmt->fetchColumn();
        return json_decode($result, true);
    }
}
```

## See Also

- [Named Colors Introduction](/docs/named-colors/introduction) - Using named colors
- [VGANamedColors Source](https://github.com/...) - Example implementation

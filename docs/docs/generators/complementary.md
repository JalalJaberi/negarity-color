---
title: Complementary Generator
sidebar_position: 2
---

# Complementary Generator

Produces the color opposite the base on the color wheel (e.g. red ↔ cyan).

## Registration

```php
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Generator\ComplementaryGenerator;

GeneratorRegistry::register(new ComplementaryGenerator());
```

## Usage

```php
use Negarity\Color\Color;
use Negarity\Color\Generator\ComplementMethod;

$color = Color::rgb(255, 0, 0);

// Default (perceptual)
$complement = $color->complementary();

// By method
$artistic = $color->complementary(ComplementMethod::Artistic);   // HSL hue + 180°
$perceptual = $color->complementary(ComplementMethod::Perceptual); // LCh
$displayAccurate = $color->complementary(ComplementMethod::DisplayAccurate); // RGB invert

// String (e.g. from config)
$complement = $color->complementary('perceptual');
```

## Methods

- **Artistic** – complement in HSL (hue + 180°).
- **Perceptual** – complement in LCh (perceptually uniform).
- **DisplayAccurate** – invert R, G, B in RGB.

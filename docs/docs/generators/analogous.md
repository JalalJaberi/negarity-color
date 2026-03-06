---
title: Analogous Generator
sidebar_position: 4
---

# Analogous Generator

Produces a color at a fixed hue offset from the base (e.g. −30° or +30°). Typically two generators are registered: `analogous1`, `analogous2`.

## Registration

```php
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Generator\AnalogousGenerator;

GeneratorRegistry::register(new AnalogousGenerator(-30));  // analogous1: base - 30°
GeneratorRegistry::register(new AnalogousGenerator(30));   // analogous2: base + 30°
```

## Usage

```php
use Negarity\Color\Color;
use Negarity\Color\Generator\AnalogousMethod;

$color = Color::rgb(255, 0, 0);

// Default (perceptual)
$a1 = $color->analogous1();
$a2 = $color->analogous2();

// By method
$a1 = $color->analogous1(AnalogousMethod::Artistic);
$a2 = $color->analogous2(AnalogousMethod::Perceptual);

// String
$a1 = $color->analogous1('artistic');
```

## Methods

- **Artistic** – analogous in HSL.
- **Perceptual** – analogous in LCh.
- **DisplayAccurate** – analogous in HSV, then to RGB.

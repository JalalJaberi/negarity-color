---
title: Triadic Generator
sidebar_position: 3
---

# Triadic Generator

Produces one of two triadic colors: base + 120° or base + 240° on the color wheel. Two generators are typically registered (e.g. `triadic1`, `triadic2`).

## Registration

```php
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Generator\TriadicGenerator;

GeneratorRegistry::register(new TriadicGenerator(120));  // triadic1: +120°
GeneratorRegistry::register(new TriadicGenerator(240));  // triadic2: +240°
```

## Usage

```php
use Negarity\Color\Color;
use Negarity\Color\Generator\TriadicMethod;

$color = Color::rgb(255, 0, 0);

// Default (perceptual)
$t1 = $color->triadic1();
$t2 = $color->triadic2();

// By method
$t1 = $color->triadic1(TriadicMethod::Artistic);
$t2 = $color->triadic2(TriadicMethod::Perceptual);

// String
$t1 = $color->triadic1('artistic');
```

## Methods

- **Artistic** – triadic in HSL (hue + 120° or + 240°).
- **Perceptual** – triadic in LCh.
- **DisplayAccurate** – triadic in HSV, then to RGB.

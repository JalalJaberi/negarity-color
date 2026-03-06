---
title: Tetradic Generator
sidebar_position: 6
---

# Tetradic Generator

Produces one of three tetradic colors from a base. Supports **square** (90°, 180°, 270°) or **rectangle** (30°, 180°, 210°) schemes. Three generators are typically registered: `tetradic1`, `tetradic2`, `tetradic3`.

## Registration

```php
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Generator\TetradicGenerator;

GeneratorRegistry::register(new TetradicGenerator(90, 30));   // tetradic1: square +90°, rectangle +30°
GeneratorRegistry::register(new TetradicGenerator(180, 180)); // tetradic2: +180° (both)
GeneratorRegistry::register(new TetradicGenerator(270, 210)); // tetradic3: square +270°, rectangle +210°
```

## Usage

```php
use Negarity\Color\Color;
use Negarity\Color\Generator\TetradicMethod;

$color = Color::rgb(255, 0, 0);

// Default (perceptual, square)
$t1 = $color->tetradic1();
$t2 = $color->tetradic2();
$t3 = $color->tetradic3();

// Square
$t1 = $color->tetradic1(TetradicMethod::Square);
$t2 = $color->tetradic2(TetradicMethod::Square);
$t3 = $color->tetradic3(TetradicMethod::Square);

// Rectangle
$t1 = $color->tetradic1(TetradicMethod::Rectangle);
$t2 = $color->tetradic2(TetradicMethod::Rectangle);
$t3 = $color->tetradic3(TetradicMethod::Rectangle);

// String
$t1 = $color->tetradic1('rectangle');
```

## Methods

- **Square** – hues at 90°, 180°, 270°.
- **Rectangle** – hues at 30°, 180°, 210°.

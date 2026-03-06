---
title: Split Complementary Generator
sidebar_position: 5
---

# Split Complementary Generator

Produces a color on one side of the complement (e.g. base + 150° or + 210°). Two generators are typically registered: `split_complementary1`, `split_complementary2`.

## Registration

```php
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Generator\SplitComplementaryGenerator;

GeneratorRegistry::register(new SplitComplementaryGenerator(150));  // split_complementary1: +150°
GeneratorRegistry::register(new SplitComplementaryGenerator(210));  // split_complementary2: +210°
```

## Usage

```php
use Negarity\Color\Color;
use Negarity\Color\Generator\SplitComplementaryMethod;

$color = Color::rgb(255, 0, 0);

// Default (perceptual)
$s1 = $color->split_complementary1();
$s2 = $color->split_complementary2();

// By method
$s1 = $color->split_complementary1(SplitComplementaryMethod::Artistic);
$s2 = $color->split_complementary2(SplitComplementaryMethod::Perceptual);

// String
$s1 = $color->split_complementary1('artistic');
```

## Methods

- **Artistic** – split complement in HSL.
- **Perceptual** – split complement in LCh.
- **DisplayAccurate** – split complement in HSV, then to RGB.

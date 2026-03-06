---
title: Monochromatic Generators (Shades, Tints, Tones)
sidebar_position: 7
---

# Monochromatic Generators

Produce shades (darker), tints (lighter), or tones (desaturated) of a base color. Each generator is registered for a step index (e.g. `mono_shade_1`, `mono_tint_2`, `mono_tone_3`). Step 1 is the first variant; the base color is the original.

## Registration

```php
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Generator\MonochromaticShadeGenerator;
use Negarity\Color\Generator\MonochromaticTintGenerator;
use Negarity\Color\Generator\MonochromaticToneGenerator;

// Steps 1–9 (max 10 colors including base)
for ($i = 1; $i <= 4; $i++) {
    GeneratorRegistry::register(new MonochromaticShadeGenerator($i));
    GeneratorRegistry::register(new MonochromaticTintGenerator($i));
    GeneratorRegistry::register(new MonochromaticToneGenerator($i));
}
```

## Usage

```php
use Negarity\Color\Color;
use Negarity\Color\Generator\MonochromaticMethod;

$color = Color::rgb(255, 0, 0);

// Default (perceptual, count 4)
$shade1 = $color->mono_shade_1();
$shade2 = $color->mono_shade_2();
$tint1 = $color->mono_tint_1();
$tone1 = $color->mono_tone_1();

// By method (string or enum)
$shade1 = $color->mono_shade_1(MonochromaticMethod::Artistic);
$tint1 = $color->mono_tint_1('perceptual');

// With options: method and count
$value = ['method' => 'perceptual', 'count' => 5];
$shade1 = $color->mono_shade_1($value);
$tint2 = $color->mono_tint_2($value);
```

## Methods

- **Artistic** – shade/tint/tone in HSL (add black/white/gray in HSL space).
- **Perceptual** – in LCh (perceptually uniform).
- **DisplayAccurate** – display-oriented computation.

## Variants

- **Shades** (`mono_shade_N`) – darken by adding black.
- **Tints** (`mono_tint_N`) – lighten by adding white.
- **Tones** (`mono_tone_N`) – desaturate by adding gray.

Step `N` gives the N-th variant (1 = first darker/lighter/toned). Default count is 4 (steps 1–3 plus base).

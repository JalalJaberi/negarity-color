---
title: Introduction to Generators
sidebar_position: 1
---

# Introduction to Generators

Generators produce related colors from a single base color—for example complementary, triadic, or monochromatic palettes. Negarity Color provides a generator system that you register with `GeneratorRegistry`; once registered, each generator is available as a method on color instances.

## What are Generators?

Generators take one color and an optional value (e.g. method or step) and return a new color. They are used to build color harmonies and palettes:

- **Complementary** – opposite hue (e.g. red ↔ cyan)
- **Triadic** – three hues 120° apart
- **Analogous** – neighboring hues (e.g. ±30°)
- **Split complementary** – complement ± offset
- **Tetradic** – four hues (square or rectangle on the color wheel)
- **Monochromatic** – shades (add black), tints (add white), or tones (add gray)

## Registering Generators

Before use, generators must be registered with `GeneratorRegistry`:

```php
use Negarity\Color\Color;
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Generator\ComplementaryGenerator;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
GeneratorRegistry::register(new ComplementaryGenerator());

$color = Color::rgb(255, 0, 0);
$complement = $color->complementary();
```

## Applying Generators

Once registered, a generator is invoked as a method on the color. The method name is the generator's name (e.g. `complementary`, `triadic1`, `mono_shade_1`):

```php
// Complementary (optional method: artistic, perceptual, display_accurate)
$complement = $color->complementary();
$complement = $color->complementary('perceptual');

// Triadic (two generators: triadic1, triadic2)
$t1 = $color->triadic1();
$t2 = $color->triadic2();

// Via registry
$gen = GeneratorRegistry::get('complementary');
$complement = $gen->apply($color, 'artistic');
```

## Built-in Generators

| Generator | Method name(s) | Description |
|----------|----------------|-------------|
| [Complementary](/docs/generators/complementary) | `complementary` | Opposite hue |
| [Triadic](/docs/generators/triadic) | `triadic1`, `triadic2` | +120° / +240° |
| [Analogous](/docs/generators/analogous) | `analogous1`, `analogous2` | Neighboring hues |
| [Split complementary](/docs/generators/split-complementary) | `split_complementary1`, `split_complementary2` | Complement ± offset |
| [Tetradic](/docs/generators/tetradic) | `tetradic1`, `tetradic2`, `tetradic3` | Square or rectangle |
| [Monochromatic](/docs/generators/monochromatic) | `mono_shade_N`, `mono_tint_N`, `mono_tone_N` | Shades, tints, tones |

## Methods (Artistic, Perceptual, Display-accurate)

Many generators support different computation methods:

- **Artistic** – often HSL-based (familiar in design tools)
- **Perceptual** – LCh-based (perceptually uniform)
- **DisplayAccurate** – display-oriented (e.g. RGB or HSV)

You can pass the method as an enum or string: `$color->complementary('perceptual')` or `$color->complementary(ComplementMethod::Perceptual)`.

## Next Steps

- Use the built-in generators: [Complementary](/docs/generators/complementary), [Triadic](/docs/generators/triadic), [Analogous](/docs/generators/analogous), [Split complementary](/docs/generators/split-complementary), [Tetradic](/docs/generators/tetradic), [Monochromatic](/docs/generators/monochromatic).
- [Add custom generators](/docs/extending/adding-generators) in Extending the library.

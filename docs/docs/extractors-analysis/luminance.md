---
title: Luminance
sidebar_position: 5
---

# Luminance extractor

The **`luminance`** extractor (`LuminanceExtractor`) returns **CIE XYZ Y** — the **relative luminance** of a color on a **0–100** scale (same numeric range as other property sliders in this library).

It answers: *“How much light does this color emit?”* in the physical, photometric sense — not *“how light does it look?”* (that is [Brightness](/docs/extractors-analysis/introduction) via LCh **L**).

---

## Formula (sRGB / D65)

The library computes **Y** from **linear** RGB (sRGB inverse gamma applied first), using the second row of the standard **sRGB → XYZ** matrix (IEC 61966-2-1, same coefficients as **Rec. 709**):

```text
Y_linear = 0.2126729·R + 0.7151522·G + 0.0721750·B
```

Then **Y** is stored as a **percentage** on 0–100 (the library’s XYZ convention), which is what the extractor returns.

```php
$xyz = $color->toXYZ();
$y = $xyz->getChannel('y'); // same as LuminanceExtractor::extract($color)
```

### Why not gamma-encoded RGB?

Applying `0.2126·R + 0.7152·G + 0.0722·B` directly to 8-bit sRGB channel values is **incorrect**. Those coefficients assume **linear** R, G, B in [0, 1]. The library’s `toXYZ()` path linearizes sRGB first.

### Why XYZ Y instead of only the matrix?

Using **`toXYZ()`** ensures:

- Correct handling of **all registered input spaces** (HSL, Lab, …) via the conversion graph
- Respect for **illuminant** and **observer** on CIE-based inputs (same as other XYZ-derived properties)

---

## End-to-end pipeline

When you call `LuminanceExtractor::extract($color)`:

1. Convert the color to **XYZ** (`toXYZ()`).
2. Read channel **Y** (0–100).
3. Clamp to `[0, 100]`.
4. **`getLabelForValue()`** maps the number to short words (very dark … very light).

There is **no** `algorithm` parameter — a single photometric definition.

---

## Luminance vs brightness

| Extractor | Source | Meaning |
|-----------|--------|---------|
| **`luminance`** | XYZ **Y** | Physical relative luminance (linear-light weighted sum) |
| **`brightness`** | LCh **L** (default), or chosen algorithm | Display / heuristic lightness — see [Brightness](/docs/extractors-analysis/brightness) |

Example: saturated **yellow** often has **high Y** (lots of green + red in linear light) while perceptual **L** can differ.

---

## Code

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\LuminanceExtractor;
use Negarity\Color\Extractor\ExtractorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
ExtractorRegistry::register(new LuminanceExtractor());

$color = Color::rgb(255, 255, 0);
$value = ExtractorRegistry::get('luminance')->extract($color);
$label = LuminanceExtractor::getLabelForValue($value);
```

---

## Example script

```bash
php examples/Extractor/Luminance.php
```

---

## See also

- [XYZ color space](/docs/references/color-spaces/xyz) — X, Y, Z channels
- [Introduction to Extractors](/docs/extractors-analysis/introduction)
- [Extractors reference](/docs/references/extractors)

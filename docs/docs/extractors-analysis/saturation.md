---
title: Saturation
sidebar_position: 3
---

# Saturation extractor

The **`saturation`** extractor (`SaturationExtractor`) estimates how **vivid** vs **achromatic** a sample appears on a **0–100** scale suited for sliders and labels (“dull”, “muted”, “moderate”, “vivid”).

Saturation here is **relative to lightness or value** in a cylindrical RGB-derived model — not the same as **chroma** (absolute colorfulness in Lab/OKLab). Use [Chroma](/docs/extractors-analysis/chroma) when you need perceptual *C* independent of the HSV/HSL definition.

---

## End-to-end pipeline

When you call `SaturationExtractor::extract($color, $params)`:

1. **Pick an algorithm** (default **HSV**).
2. **Convert** the input color to HSV or HSL (`toHSV()` or `toHSL()`).
3. **Read the S channel** — already stored on a **0–100** scale in the library.
4. **Clamp** to `[0, 100]`.
5. **Labels** — `SaturationExtractor::getLabelForValue($value)` maps the number to short words.

---

## Algorithm A — HSV (default)

**Registry key / constant:** `hsv` · `SaturationExtractor::ALGORITHM_HSV`

**Reference:** Alvy Ray Smith, *Color Gamut Transform Pairs*, SIGGRAPH 1978.

Given linear-normalized RGB channels *r*, *g*, *b* ∈ [0, 1]:

```text
V = max(r, g, b)
S = 0                         if V = 0
S = (V − min(r, g, b)) / V    otherwise
```

The library’s `toHSV()` returns **S** and **V** as percentages (0–100). The extractor returns that **S** value directly.

```php
$hsv = $color->toHSV();
$s = $hsv->getChannel('s'); // same magnitude as extract() with default algorithm
```

**When it’s useful:** classic “amount of pure hue vs white” tied to **value**; common in pickers and image tools.

---

## Algorithm B — HSL

**Registry key / constant:** `hsl` · `SaturationExtractor::ALGORITHM_HSL`

**Reference:** James D. Foley & Andries van Dam, *Fundamentals of Interactive Computer Graphics* (1982) / standard HSL derivation used in graphics texts.

```text
L = (max + min) / 2
S = 0                                    if max = min
S = (max − min) / (max + min)            if L ≤ 0.5
S = (max − min) / (2 − max − min)        if L > 0.5
```

Again, `toHSL()` exposes **S** on 0–100; the extractor reads and clamps it.

**When it’s useful:** saturation relative to **lightness** — pastels at mid-lightness can read differently than in HSV.

**Aliases:** `foley`, `vandam`, …

---

## HSV vs HSL on the same color

For **primary sRGB colors** at full value/lightness (e.g. `rgb(255, 0, 0)`), both algorithms usually return **100**.

They **diverge** for mid-lightness tints and shades — e.g. pastel pink may show lower HSL *S* than HSV *S* because HSL divides by a smaller chroma range when *L* is near 0.5.

---

## Choosing an algorithm in code

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\SaturationExtractor;

$extractor = new SaturationExtractor();
$color = Color::rgb(255, 200, 210);

// Default: HSV (Smith, 1978)
$a = $extractor->extract($color);

// HSL (Foley & van Dam)
$b = $extractor->extract($color, [
    'algorithm' => SaturationExtractor::ALGORITHM_HSL,
]);
```

`SaturationExtractor::getAlgorithmLabel($algorithm)` returns display names.

If `algorithm` is omitted or unknown, the implementation uses **HSV**.

---

## Example script

```bash
php examples/Extractor/Saturation.php
```

---

## See also

- [Chroma](/docs/extractors-analysis/chroma) — absolute colorfulness (OKLCH, Lab, Luv)
- [Introduction to Extractors](/docs/extractors-analysis/introduction)
- [Extractors reference](/docs/references/extractors)
- [HSV color space](/docs/references/color-spaces/hsv) · [HSL color space](/docs/references/color-spaces/hsl)

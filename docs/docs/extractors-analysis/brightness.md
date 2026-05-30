---
title: Brightness
sidebar_position: 6
---

# Brightness extractor

The **`brightness`** extractor (`BrightnessExtractor`) estimates how **light** vs **dark** a sample appears on a **0–100** scale suited for sliders and labels (“very dark”, “medium”, “very light”).

These are **display / heuristic brightness** models — ways to derive a single scalar from RGB or perceptual spaces when full color appearance modeling is optional. They are **not** the same as physical [luminance](/docs/extractors-analysis/luminance) (CIE XYZ **Y** on linear light).

Default algorithm: **LCh L** (perceptual lightness from CIE L\*a\*b\* — the library’s original behaviour).

---

## End-to-end pipeline

When you call `BrightnessExtractor::extract($color, $params)`:

1. **Pick an algorithm** (default **lch**).
2. **Convert** if needed (`toRGB()`, `toHSV()`, `toLab()`, `toLCh()`, `toXYZ()`).
3. **Apply the formula** for that algorithm.
4. **Clamp** to `[0, 100]`.
5. **Labels** — `BrightnessExtractor::getLabelForValue($value)` maps the number to short words.

---

## Simple heuristics (gamma-encoded RGB)

All formulas below use **8-bit sRGB** channels (0–255) from `toRGB()`, then scale the result to **0–100**.

### Algorithm A — RGB average

**Registry key / constant:** `average` · `BrightnessExtractor::ALGORITHM_AVERAGE`

**Reference:** common engineering heuristic (no formal standard).

```text
B = (R + G + B) / 3
```

Treats all channels equally. Fast but not perceptually accurate.

**Aliases:** `rgb`, `mean`, `grayscale`

---

### Algorithm B — Lightness (max–min midpoint)

**Registry key / constant:** `lightness` · `BrightnessExtractor::ALGORITHM_LIGHTNESS`

**Reference:** HSV/HSL-style systems (Smith, 1978; Tektronix HSV).

```text
B = (max(R, G, B) + min(R, G, B)) / 2
```

Captures the midpoint of the channel range — closer to perceived lightness than a plain average.

**Aliases:** `maxmin`, `midpoint`

---

### Algorithm C — HSV value (max channel)

**Registry key / constant:** `hsvValue` · `BrightnessExtractor::ALGORITHM_HSV_VALUE`

**Reference:** Alvy Ray Smith, *Color Gamut Transform Pairs*, SIGGRAPH 1978.

```text
B = max(R, G, B)
```

Same as HSV **V** on the library’s 0–100 scale (`toHSV()->getChannel('v')`).

**Aliases:** `value`, `max`, `hsv`, `hsb`, `v`

---

## Weighted video luma (gamma-encoded RGB)

These apply classic **luma coefficients directly to display RGB** — a common “brightness” shortcut in graphics. For **linear-light** relative luminance use [Luminance](/docs/extractors-analysis/luminance) instead.

### Algorithm D — Rec. 601

**Registry key / constant:** `rec601` · `BrightnessExtractor::ALGORITHM_REC601`

**Reference:** ITU-R BT.601 (legacy SD video).

```text
Y = 0.299·R + 0.587·G + 0.114·B
```

**Aliases:** `bt601`, `itu601`, `601`

---

### Algorithm E — Rec. 709

**Registry key / constant:** `rec709` · `BrightnessExtractor::ALGORITHM_REC709`

**Reference:** ITU-R BT.709 (HDTV / sRGB workflows).

```text
Y = 0.2126·R + 0.7152·G + 0.0722·B
```

**Aliases:** `bt709`, `itu709`, `709`, `srgbluma`

---

## Perceptual models

### Algorithm F — LCh L (default)

**Registry key / constant:** `lch` · `BrightnessExtractor::ALGORITHM_LCH`

Uses the **L** channel from CIE L\*a\*b\* via `toLCh()` (polar form of Lab). Nonlinear response aligned with human lightness perception; depends on illuminant/observer on the conversion path.

```php
$l = $color->toLCh()->getChannel('l'); // same as extract() with default algorithm
```

**When it’s useful:** perceptual sliders, theming, accessibility copy — previous library default.

**Aliases:** `lstar`, `l*`, `perceptual`

---

### Algorithm G — CIE 1976 L\*a\*b\* L\*

**Registry key / constant:** `cie1976Lab` · `BrightnessExtractor::ALGORITHM_CIE1976_LAB`

```text
L* = 116·f(Y/Yn) − 16   (via Lab conversion)
```

Reads **L\*** from `toLab()->getChannel('l')`. Numerically matches LCh **L** for the same color.

**Aliases:** `lab`, `cielab`, `lab1976`

---

### Algorithm H — CIECAM02 J

**Registry key / constant:** `ciecam02` · `BrightnessExtractor::ALGORITHM_CIECAM02`

**Reference:** CIE colour appearance model CIECAM02 (Fairchild, Luo, Moroney, …).

Computes the **lightness correlate J** from XYZ through chromatic adaptation, cone compression, and achromatic response. Default viewing: D65 white, average surround, `Y_b` = 20, `L_A` ≈ 4.07 cd/m².

Optional `$params`: `L_A`, `Y_b` (cd/m² and background **Y** factor).

**Aliases:** `cam02`, `cam2002`

---

### Algorithm I — CIECAM16 J

**Registry key / constant:** `ciecam16` · `BrightnessExtractor::ALGORITHM_CIECAM16`

**Reference:** CIE 248:2022 CIECAM16.

Same role as CIECAM02 **J** with the updated CAT16 / response pipeline. Same default viewing parameters and optional `L_A`, `Y_b`.

**Aliases:** `cam16`

---

## Continuum: fast → perceptually correct

| Layer | Algorithms | Trade-off |
|-------|------------|-----------|
| **Heuristics** | `average`, `lightness`, `hsvValue` | Wrong but fast |
| **Video luma** | `rec601`, `rec709` | Simple weighted RGB |
| **Perceptual** | `lch`, `cie1976Lab`, `ciecam02`, `ciecam16` | Psychophysically motivated |

---

## Choosing an algorithm in code

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\BrightnessExtractor;

$extractor = new BrightnessExtractor();
$color = Color::rgb(255, 200, 50);

// Default: LCh L (library legacy)
$a = $extractor->extract($color);

// Rec. 709 on gamma-encoded RGB
$b = $extractor->extract($color, [
    'algorithm' => BrightnessExtractor::ALGORITHM_REC709,
]);

// CIECAM16 with custom viewing
$c = $extractor->extract($color, [
    'algorithm' => BrightnessExtractor::ALGORITHM_CIECAM16,
    'L_A' => 318.31,
    'Y_b' => 20.0,
]);
```

`BrightnessExtractor::getAlgorithmLabel($algorithm)` returns display names (`Rec. 709 luma`, `CIECAM16 J`, …).

If `algorithm` is omitted or unknown, the implementation uses **lch**.

---

## Brightness vs luminance

| Extractor | Source | Meaning |
|-----------|--------|---------|
| **`brightness`** | Chosen algorithm above | Display / heuristic “how light?” |
| **`luminance`** | XYZ **Y** (linear sRGB → XYZ) | Physical relative luminance |

Example: saturated **yellow** often scores high on Rec. 709 and on XYZ **Y**, while perceptual **L\*** can differ.

---

## Example script

```bash
php examples/Extractor/Brightness.php
```

---

## See also

- [Luminance](/docs/extractors-analysis/luminance) — CIE XYZ Y (linear RGB)
- [Saturation](/docs/extractors-analysis/saturation) — HSV / HSL vividness
- [Chroma](/docs/extractors-analysis/chroma) — absolute colorfulness
- [Introduction to Extractors](/docs/extractors-analysis/introduction)
- [Extractors reference](/docs/references/extractors)
- [HSV color space](/docs/references/color-spaces/hsv) · [Lab color space](/docs/references/color-spaces/lab)

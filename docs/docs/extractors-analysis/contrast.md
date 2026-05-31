---
title: Contrast
sidebar_position: 9
---

# Contrast extractor

The **`contrast`** extractor (`ContrastExtractor`) measures how distinguishable a **foreground** color is from a **reference** (background). By default the library compares against **white** or **black** — the same pairing used on color detail pages and in the WordPress theme.

Multiple **algorithms** are available; each answers a slightly different question (accessibility ratio vs luminance difference vs perceptual distance in Lab).

---

## End-to-end pipeline

When you call `ContrastExtractor::extract($color, $params)`:

1. **Resolve** the algorithm (default **WCAG contrast ratio**) and reference (`white`, `black`, or another `ColorInterface`).
2. **Convert** both colors as needed (RGB relative luminance for WCAG/Michelson/Weber/RMS; Lab for ΔE76).
3. **Compute** the metric and return a scalar.
4. **Labels** — `ContrastExtractor::getLabelForValue($value, $algorithm)` maps to WCAG bands, ΔE buckets, or generic contrast words.

---

## Reference color (`contrastWith`)

| `$params` | Reference |
|-----------|-----------|
| `null` or `'white'` | White sRGB (255, 255, 255) |
| `'black'` | Black sRGB (0, 0, 0) |
| `ColorInterface` | That color |
| Array with `contrastWith` / `reference` | Same rules |

Legacy calls like `extract($color, 'black')` still use **WCAG** (backward compatible).

```php
// WCAG vs white (legacy)
$ratio = ExtractorRegistry::get('contrast')->extract($color, 'white');

// Michelson vs black (array)
$michelson = ExtractorRegistry::get('contrast')->extract($color, [
    'algorithm' => ContrastExtractor::ALGORITHM_MICHELSON_CONTRAST,
    'contrastWith' => 'black',
]);
```

---

## Algorithm A — WCAG contrast ratio (default)

**Key:** `wcagContrastRatio` · `ContrastExtractor::ALGORITHM_WCAG_CONTRAST_RATIO`

WCAG 2.x relative luminance ratio using sRGB linearization:

```text
L = 0.2126·R + 0.7152·G + 0.0722·B   (linearized channels)
ratio = (max(L₁, L₂) + 0.05) / (min(L₁, L₂) + 0.05)
```

**Range:** about **1–21**. Labels: `fails`, `AA large`, `AA`, `AAA`.

**Aliases:** `wcag`, `ratio`

---

## Algorithm B — Michelson contrast

**Key:** `michelsonContrast` · `ContrastExtractor::ALGORITHM_MICHELSON_CONTRAST`

Classic Michelson formula on **relative luminance** (not the inverted form):

```text
C = (L_max − L_min) / (L_max + L_min) × 100
```

**Range:** **0–100** (percent). Black vs white → 100.

**Aliases:** `michelson`

---

## Algorithm C — Weber contrast

**Key:** `weberContrast` · `ContrastExtractor::ALGORITHM_WEBER_CONTRAST`

Weber’s law on relative luminance — foreground is the input color, background is the reference:

```text
C = (L_target − L_background) / L_background × 100
```

Background luminance is floored at **0.05** to avoid division by zero on pure black.

**Range:** unbounded (often **−100…400** on sliders). Negative values mean the foreground is darker than the background.

**Aliases:** `weber`

---

## Algorithm D — RMS contrast

**Key:** `rmsContrast` · `ContrastExtractor::ALGORITHM_RMS_CONTRAST`

Root-mean-square contrast between two luminance levels:

```text
C = |L₁ − L₂| / √(½(L₁² + L₂²)) × 100
```

**Range:** **0–100**. Differs from Michelson for mid-range pairs.

**Aliases:** `rms`

---

## Algorithm E — ΔE76 (CIE 1976)

**Key:** `deltaE76` · `ContrastExtractor::ALGORITHM_DELTA_E76`

Perceptual distance in **CIE L\*a\*b\*** (CIE76):

```text
ΔE = √((ΔL*)² + (Δa*)² + (Δb*)²)
```

**Range:** **0–100+** (typical display cap 100 on sliders). Labels: `not perceptible`, `small`, `noticeable`, `large`.

**Aliases:** `deltaE`, `cie76`, `de76`

---

## White and black together

On Negarity color pages, **contrast vs white** and **contrast vs black** are shown as **two separate sliders** — each can link to the contrast calculator. The interactive **Contrast Extractor** block and REST API return **both references** for every selected algorithm.

---

## Example script

Run `php examples/Extractor/Contrast.php` for WCAG, Michelson, Weber, RMS, and ΔE76 vs white and black on sample colors.

---

## See also

- [Luminance](/docs/extractors-analysis/luminance) — single-color CIE XYZ Y
- [Brightness](/docs/extractors-analysis/brightness) — display lightness
- [Extractors Reference](/docs/references/extractors)

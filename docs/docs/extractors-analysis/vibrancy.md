---
title: Vibrancy
sidebar_position: 8
---

# Vibrancy extractor

The **`vibrancy`** extractor (`VibrancyExtractor`) estimates how **dull** vs **vibrant** a color appears on a **0–100** scale. Vibrancy is highest when a color is both **colorful** (high chroma) and at **mid lightness** — very light tints and very dark shades read as less vibrant even at the same chroma.

This is a **design heuristic** for UI copy and theming, not a CIE standard. Both algorithms use LCh **L** and **c** via `toLCh()`.

---

## End-to-end pipeline

When you call `VibrancyExtractor::extract($color, $params)`:

1. **Pick an algorithm** (default **midtoneChromaIndex**).
2. **Convert** the input color to LCh.
3. **Normalize chroma** `C_norm = min(1, C* / 130)`.
4. **Apply** a lightness envelope (triangular or Gaussian) and scale to 0–100.
5. **Labels** — `VibrancyExtractor::getLabelForValue($value)` maps to `low`, `moderate`, `high`, or `vibrant`.

---

## Algorithm A — Midtone chroma index (default)

**Registry key / constant:** `midtoneChromaIndex` · `VibrancyExtractor::ALGORITHM_MIDTONE_CHROMA_INDEX`

Triangular peak at L* = 50:

```text
C_norm = min(1, C* / 130)
midPeak = max(0, 1 − 2|L* / 100 − 0.5|)
V       = C_norm × midPeak × 100
```

**When it's useful:** simple, fast model — vibrancy drops linearly toward black and white.

**Aliases:** `midtone`, `triangle`, `chroma_index`

---

## Algorithm B — Gaussian vibrancy index

**Registry key / constant:** `gaussianVibrancyIndex` · `VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX`

Smooth bell-shaped lightness envelope:

```text
C_norm  = min(1, C* / 130)
envelope = exp(−(L* − μ)² / (2σ²))
V        = C_norm × envelope × 100
```

Default **μ = 50**, **σ = 25** (L* units). Pass **`mu`** and **`sigma`** in `$params` to tune the peak position and width.

**When it's useful:** softer falloff at extremes — pastels and deep shades retain more vibrancy than the triangular model.

**Aliases:** `gaussian`, `gauss`, `normal`

---

## Midtone vs Gaussian

For **mid-lightness saturated** colors (e.g. `rgb(0, 255, 0)`), both algorithms usually agree.

They **diverge** for **light tints** and **dark shades**: Gaussian keeps more vibrancy away from L* = 50 because the envelope decays smoothly rather than linearly to zero at L* = 0 and 100.

---

## Choosing an algorithm in code

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\VibrancyExtractor;
use Negarity\Color\Extractor\ExtractorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
ExtractorRegistry::register(new VibrancyExtractor());

$color = Color::rgb(255, 0, 0);

$midtone = ExtractorRegistry::get('vibrancy')->extract($color);

$gaussian = ExtractorRegistry::get('vibrancy')->extract($color, [
    'algorithm' => VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX,
    'mu' => 50,
    'sigma' => 25,
]);

echo VibrancyExtractor::getLabelForValue($gaussian);
echo VibrancyExtractor::getAlgorithmLabel(
    VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX
);
```

---

## Example script

Run `php examples/Extractor/Vibrancy.php` for a table comparing both algorithms on sample colors.

---

## See also

- [Chroma](/docs/extractors-analysis/chroma) — absolute colorfulness without lightness envelope
- [Saturation](/docs/extractors-analysis/saturation) — HSV/HSL vividness
- [Extractors Reference](/docs/references/extractors)

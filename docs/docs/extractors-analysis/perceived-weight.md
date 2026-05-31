---
title: Perceived weight
sidebar_position: 7
---

# Perceived weight extractor

The **`perceived_weight`** extractor (`PerceivedWeightExtractor`) estimates how **visually heavy** a color feels on a **0–100** scale: darker and more saturated samples tend to read as **heavier**; light neutrals read as **light**.

This is a **design heuristic**, not a CIE standard. Both algorithms use CIE LCh **L** as lightness (L*) and **c** as chroma (C*) via `toLCh()`.

---

## End-to-end pipeline

When you call `PerceivedWeightExtractor::extract($color, $params)`:

1. **Pick an algorithm** (default **brightnessChromaLinear**).
2. **Convert** the input color to LCh (`toLCh()`).
3. **Compute darkness** `100 − L*` and **normalized chroma** `min(1, C* / 130)`.
4. **Apply** the chosen formula and **clamp** to `[0, 100]`.
5. **Labels** — `PerceivedWeightExtractor::getLabelForValue($value)` maps to `light`, `medium`, or `heavy`.

---

## Algorithm A — Brightness + chroma (linear, default)

**Registry key / constant:** `brightnessChromaLinear` · `PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_LINEAR`

Weighted sum of darkness and chroma:

```text
darkness   = 100 − L*
chromaNorm = min(1, C* / 130)
W          = 0.7 · darkness + 0.3 · chromaNorm · 100
```

**When it's useful:** stable, additive blend — chroma adds up to 30 points on top of darkness; good default for UI sliders.

**Aliases:** `linear`, `sum`, `additive`

---

## Algorithm B — Brightness × chroma (multiplication)

**Registry key / constant:** `brightnessChromaMultiplication` · `PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION`

Multiplicative chroma boost on darkness:

```text
darkness   = 100 − L*
chromaNorm = min(1, C* / 130)
W          = darkness × (1 + k · chromaNorm)
```

Default **`k = 0.5`**. Pass **`k`** in `$params` to tune how much saturation increases heaviness at a given lightness.

**When it's useful:** saturated dark colors (e.g. deep red, navy) feel disproportionately heavier than the linear model suggests.

**Aliases:** `multiplication`, `multiplicative`, `multiply`, `product`

---

## Linear vs multiplication

For **white**, **black**, and **neutral grays**, both algorithms agree (chroma ≈ 0).

They **diverge** for **saturated mid-to-dark** colors: multiplication amplifies heaviness when both darkness and chroma are high.

---

## Choosing an algorithm in code

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\PerceivedWeightExtractor;
use Negarity\Color\Extractor\ExtractorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
ExtractorRegistry::register(new PerceivedWeightExtractor());

$color = Color::rgb(128, 0, 0);

$linear = ExtractorRegistry::get('perceived_weight')->extract($color);
// same as explicit algorithm:
$linear = ExtractorRegistry::get('perceived_weight')->extract($color, [
    'algorithm' => PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_LINEAR,
]);

$multiplication = ExtractorRegistry::get('perceived_weight')->extract($color, [
    'algorithm' => PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION,
    'k' => 0.5,
]);

echo PerceivedWeightExtractor::getLabelForValue($multiplication); // e.g. "heavy"
echo PerceivedWeightExtractor::getAlgorithmLabel(
    PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION
);
```

---

## Example script

Run `php examples/Extractor/PerceivedWeight.php` for a table comparing both algorithms on sample colors.

---

## See also

- [Brightness](/docs/extractors-analysis/brightness) — lightness without chroma weighting
- [Chroma](/docs/extractors-analysis/chroma) — absolute colorfulness
- [Saturation](/docs/extractors-analysis/saturation) — vividness in HSV/HSL
- [Extractors Reference](/docs/references/extractors)

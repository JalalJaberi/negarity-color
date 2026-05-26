---
title: Chroma
sidebar_position: 4
---

# Chroma extractor

The **`chroma`** extractor (`ChromaExtractor`) estimates how **neutral** vs **colored** a sample appears on a **0–100** scale suited for sliders and labels (“neutral”, “low”, “medium”, “high”, “colory”).

It is **not** the same as **saturation** (which is relative to lightness in LCh/HSL). Chroma here is the **absolute colorfulness** magnitude in a chosen perceptual space: the length of the chromatic vector *(a, b)* or *(u\*, v\*)*.

---

## End-to-end pipeline

When you call `ChromaExtractor::extract($color, $params)`:

1. **Pick an algorithm** (default **OKLCH** / OKLab).
2. **Convert** the input color to the target space (`toOklab()`, `toLab()`, or `toLuv()`).
3. **Compute chroma magnitude**  
   - OKLCH: *C* = √(a² + b²) in OKLab (same as the `c` channel on `toOklch()`)  
   - Lab: *C\** = √(a² + b²) in CIE 1976 L\*a\*b\*  
   - Luv: *C\*uv* = √(u\*² + v\*²) in CIE 1976 L\*u\*v\*
4. **Normalize to 0–100** with a fixed divisor, then clamp.

5. **Labels** — `ChromaExtractor::getLabelForValue($value)` maps the number to short words.

---

## Algorithm A — OKLCH (default)

**Registry key / constant:** `oklch` · `ChromaExtractor::ALGORITHM_OKLCH`

Uses **OKLab** (Björn Ottosson, 2020) via the library’s `toOklab()` / `toOklch()` path (linear sRGB → OKLab, matching CSS Color 4).

1. Read rectangular OKLab channels **a**, **b** (or use **c** from `toOklch()` — same magnitude).

2. Chroma magnitude:

   ```text
   C = √(a² + b²)
   ```

3. Map to 0–100:

   ```text
   value = clamp((C / 0.4) × 100, 0, 100)
   ```

The **0.4** divisor targets typical sRGB-gamut peaks better than a fixed **150** Lab scale.

```php
$oklab = $color->toOklab();
$a = $oklab->getChannel('a');
$b = $oklab->getChannel('b');
$chroma = sqrt($a * $a + $b * $b);
// same C as $color->toOklch()->getChannel('c')
```

**When it’s useful:** perceptually uniform colorfulness for UI; default for new code.

---

## Algorithm B — CIE 1976 L\*a\*b\*

**Registry key / constant:** `cie1976Lab` · `ChromaExtractor::ALGORITHM_CIE1976_LAB`

This is the **previous library default** (Lab *C\** with a **150** normalization).

1. Convert to **Lab** (`toLab()`), respecting illuminant / observer on the color.

2. *C\** = √(a² + b²)

3. `value = clamp((C* / 150) × 100, 0, 100)`

**Aliases:** `lab`, `cielab`, `cstar`, …

---

## Algorithm C — CIE 1976 L\*u\*v\*

**Registry key / constant:** `cie1976Luv` · `ChromaExtractor::ALGORITHM_CIE1976_LUV`

1. Convert to **Luv** (`toLuv()`).

2. *C\*uv* = √(u\*² + v\*²)

3. Same **150** normalization as Lab.

**Aliases:** `luv`, `cieluv`, …

---

## Choosing an algorithm in code

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\ChromaExtractor;

$extractor = new ChromaExtractor();
$color = Color::rgb(255, 0, 0);

// Default: OKLCH
$a = $extractor->extract($color);

// CIE 1976 Lab (legacy)
$b = $extractor->extract($color, [
    'algorithm' => ChromaExtractor::ALGORITHM_CIE1976_LAB,
]);

// CIE 1976 Luv
$c = $extractor->extract($color, [
    'algorithm' => ChromaExtractor::ALGORITHM_CIE1976_LUV,
]);
```

`ChromaExtractor::getAlgorithmLabel($algorithm)` returns display names (`OKLCH`, `CIE 1976 L*a*b*`, …).

If `algorithm` is omitted or unknown, the implementation uses **OKLCH**.

---

## Example script

```bash
php examples/Extractor/Chroma.php
```

---

## See also

- [Introduction to Extractors](/docs/extractors-analysis/introduction)
- [Extractors reference](/docs/references/extractors)
- [Temperature](/docs/extractors-analysis/temperature) — multi-algorithm extractor pattern

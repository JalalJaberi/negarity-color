---
title: Temperature
sidebar_position: 2
---

# Temperature extractor

The **`temperature`** extractor (`TemperatureExtractor`) estimates how **warm** or **cool** a color appears compared to [black-body (Planckian) light](https://en.wikipedia.org/wiki/Color_temperature), and returns a **signed value between −1 and 1** suited for sliders and labels (“cold”, “warm”, …).

It is **not** a physical measurement of heat; it is a **correlated color temperature (CCT)** style summary of the color’s **chromaticity** in **CIE 1931 (x, y)** space, after converting the color to **XYZ** with the library’s standard path (including illuminant / observer where relevant).

---

## End-to-end pipeline (step by step)

What happens when you call `TemperatureExtractor::extract($color, $params)`:

1. **Convert the color to XYZ**  
   The input `ColorInterface` is converted with `toXYZ()`. Channel values are the library’s **XYZ tristimulus** representation (consistent with sRGB → linear RGB → XYZ for RGB-based colors, and the usual matrices).

2. **Guard the zero-luminance case**  
   If *X + Y + Z* is effectively zero (e.g. black), the extractor returns **`0.0`** immediately (neutral on the signed scale).

3. **Compute CIE 1931 chromaticity**  
   Normalized coordinates (independent of brightness):

   ```text
   x = X / (X + Y + Z)
   y = Y / (X + Y + Z)
   ```

   These describe **where** the color sits in the chromaticity diagram, not how bright it is.

4. **Estimate correlated temperature in Kelvin**  
   Several methods are supported (see below): **McCamy** (`original` or `refined`), **nearest Planckian UCS**, and **Krystek (1985)**. Each produces a scalar *T* in **Kelvin**.

5. **Clamp Kelvin before mapping**  
   The value *T* is clamped to **500 … 100 000 K** (implementation detail for numerical stability and UI consistency).

6. **Map Kelvin to the signed UI range [−1, 1]**  
   The implementation uses a reference neutral near daylight and a scale factor *S*:

   ```text
   signed = clamp((T_neutral − T) / S, −1, 1)
   ```

   with **T_neutral = 6500 K** and **S = 4200 K**.  
   So **lower Kelvin** (more amber / “warmer” light) tends toward **more positive** values, and **higher Kelvin** (more blue / “cooler” light) toward **more negative** values.

7. **Labels**  
   `TemperatureExtractor::getLabelForValue($signed)` maps the signed number to short words (`cold`, `cool`, `neutral`, `warm`, `hot`). Thresholds are aligned to the signed scale, not to Kelvin directly.

---

## Algorithm A — McCamy approximation (default)

**Registry name / constant:** `mccamy` · `TemperatureExtractor::ALGORITHM_MCCAMY`

This path stays entirely in **CIE 1931 (x, y)** chromaticity. Pass **`version`** in `$params` to pick the cubic coefficients (default **`original`**).

| `version` | Constant | Summary |
|-----------|----------|---------|
| `original` (default) | `VERSION_ORIGINAL` | McCamy (canonical **1992**) |
| `refined` | `VERSION_REFINED` | Updated cubic (previous Negarity default) |

**Aliases (case-insensitive):** `refined` also accepts `updated`, `current`; `original` accepts `mccamy1992`, `canonical`, …

Shared reference white: **x_e = 0.3320**, **y_e = 0.1858**.

**Off-locus fallback (both versions):** if distance to the Planckian locus in CIE 1960 (u,v) exceeds **0.01**, the cubic result is discarded and Kelvin comes from the **nearest-locus UCS search** (algorithm B), so saturated blue no longer maps to spurious ~1700 K / “hot”.

### McCamy original (1992) — `version` = `original`

1. Compute the chromaticity factor:

   ```text
   n = (x − x_e) / (y − y_e)
   ```

   If *y − y_e* is numerically zero, return **6500 K** (neutral fallback).

2. Evaluate the **1992** cubic (McCamy, canonical):

   ```text
   T = −449·n³ + 3525·n² − 6823.3·n + 5520.33
   ```

3. Clamp *T* to **[1000, 25 000] K**, then map to the signed scale.

### McCamy refined — `version` = `refined`

Same pipeline as **original**, but different coefficients and clamping. Use this when you want the behaviour that Negarity used before `version` was introduced.

1. Same **n** and reference white as above. If *y − y_e* ≈ 0, use the library neutral Kelvin constant (**6500 K**).

2. Evaluate the **refined** cubic:

   ```text
   T = −437·n³ + 3601·n² − 6861·n + 5514.31
   ```

3. Clamp *T* to **[1000, 250 000] K**, then map to the signed scale.

Near whites and grays, **original** and **refined** usually agree within a few signed units; they diverge more for colours farther from the Planckian locus (before the UCS fallback kicks in).

```php
// McCamy original (1992) — default when version is omitted
$original = $extractor->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY,
    'version' => TemperatureExtractor::VERSION_ORIGINAL,
]);

// McCamy refined
$refined = $extractor->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY,
    'version' => TemperatureExtractor::VERSION_REFINED,
]);
```

**When McCamy is useful:** fast, closed form, good for **near-white** and **near-daylight** chromaticities.

**Caveat:** far from the black-body locus, CCT is not meaningful; the off-locus fallback improves saturated RGB, but remains a geometric summary.

---

## Algorithm B — Nearest Planckian locus in CIE 1960 UCS

**Registry name / constant:** `nearestPlanckianUcs1960` · `TemperatureExtractor::ALGORITHM_NEAREST_PLANCKIAN_UCS1960`

This path interprets “temperature” as: **which Planckian (black-body) chromaticity best matches the sample** in a **uniform chromaticity scale**.

1. **CIE 1931 → CIE 1960 UCS**  
   The sample *(x, y)* is converted to *(u, v)* in 1960 UCS using the standard projective relation (denominator **−2x + 12y + 3** in the implementation).

2. **Planckian locus in (x, y) for a candidate T**  
   For many temperatures *T* along the locus, *x(T)* and *y(T)* are evaluated using **Kim / Bruce Lindbloom**-style polynomials for the black-body chromaticity curve (piecewise in *T*).

3. **Same UCS for the locus**  
   Each planckian *(x(T), y(T))* is mapped to *(u_p(T), v_p(T))* with the same **1931 → 1960 UCS** transform.

4. **Search for best T**  
   The implementation minimizes **squared Euclidean distance** in **(u, v)** between the **sample** and the **locus**:

   - **Coarse** sweep from **1000 K** to **25 000 K** in steps of **40 K**.
   - **Fine** sweep around the best coarse *T* within **±120 K** in steps of **1 K**.

5. The winning *T* (in Kelvin) is passed to the **same signed mapping** as McCamy.

**When it’s useful:** explicitly ties the answer to **distance to the Planckian locus in UCS**, which is a geometric, “nearest black-body” reading of the chromaticity.

**Caveat:** more work than McCamy; for highly saturated RGB primaries, the “nearest” planckian can still be a **very approximate** summary of perceived warmth (same fundamental limitation of CCT off the locus).

---

## Algorithm C — Krystek (1985)

**Registry name / constant:** `krystek1985` · `TemperatureExtractor::ALGORITHM_KRYSTEK1985`

Krystek fits **CIE 1960 (u,v)** as a function of correlated temperature *T* using **rational polynomials** (the published Chebyshev-style approximation). There is **no closed inverse**, so the library finds *T* by **iterative search** on **[1000, 15000] K**:

1. Convert sample *(x,y)* → *(u,v)* (1960 UCS).
2. **Coarse** sweep minimizing **|uv(T) − uv_sample|**.
3. **Golden-section** refinement around the best coarse *T*.

Valid only in **1000–15 000 K** (values are clamped to that range before mapping to the signed scale).

```php
$extractor->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_KRYSTEK1985,
    'version' => TemperatureExtractor::VERSION_CHEBYSHEV, // default
]);
```

---

## Choosing an algorithm in code

Pass an **array** with an **`algorithm`** key as the second argument to `extract()`:

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\TemperatureExtractor;

$extractor = new TemperatureExtractor();
$color = Color::rgb(255, 245, 235);

// Default: McCamy + version original (1992)
$a = $extractor->extract($color);
$b = $extractor->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY,
    'version' => TemperatureExtractor::VERSION_REFINED,
]);

// Nearest Planckian locus (UCS 1960)
$c = $extractor->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_NEAREST_PLANCKIAN_UCS1960,
]);

// Krystek (1985)
$d = $extractor->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_KRYSTEK1985,
]);
```

**Aliases** for the UCS algorithm include (case-insensitive): `ucs1960`, `brute`, `brute_force`, `planckian_locus`, … — see [Extractors reference](/docs/references/extractors#temperature-extractor-parameters) for the full list.

If `algorithm` is omitted or unknown, the implementation uses **McCamy** with **`version` = `original`**.

---

## Example script

Run the shipped example to compare McCamy (**original** and **refined**), Krystek, and UCS on sample colors:

```bash
php examples/Extractor/Temperature.php
```

---

## See also

- [Introduction to Extractors](/docs/extractors-analysis/introduction)
- [Extractors reference](/docs/references/extractors)
- `examples/Extractor/Temperature.php` in the repository

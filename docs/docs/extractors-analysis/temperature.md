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
   Two algorithms are supported (see below). Both produce a single scalar *T* in **Kelvin**.

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

This path stays entirely in **(x, y)** chromaticity space and applies the **McCamy cubic** formula, a common engineering approximation from CIE 1931 chromaticity to **CCT (Kelvin)** for colors **near the Planckian locus**.

1. Compute  
   
   ```text
   n = (x − x_e) / (y − y_e)
   ```  
   with **x_e = 0.3320**, **y_e = 0.1858**.  
   If *y − y_e* is numerically zero, the implementation falls back to the **neutral Kelvin** (6500 K) for stability.

2. Evaluate the cubic (coefficients as in the implementation):

   ```text
   T = −437·n³ + 3601·n² − 6861·n + 5514.31
   ```

3. **Clamp** *T* to **[1000, 250 000] K** before the signed clamp/mapping step.

**When it’s useful:** fast, closed form, good for **near-white** and **near-daylight** chromaticities; widely used in imaging pipelines.

**Caveat:** far from the black-body locus (strongly saturated screen colors), CCT becomes less meaningful; the value is still a deterministic function of *(x, y)*.

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

## Choosing an algorithm in code

Pass an **array** with an **`algorithm`** key as the second argument to `extract()`:

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\TemperatureExtractor;

$extractor = new TemperatureExtractor();
$color = Color::rgb(255, 245, 235);

// Default: McCamy
$a = $extractor->extract($color);
$b = $extractor->extract($color, ['algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY]);

// Nearest Planckian locus (UCS 1960)
$c = $extractor->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_NEAREST_PLANCKIAN_UCS1960,
]);
```

**Aliases** for the UCS algorithm include (case-insensitive): `ucs1960`, `brute`, `brute_force`, `planckian_locus`, … — see [Extractors reference](/docs/references/extractors#temperature-extractor-parameters) for the full list.

If `algorithm` is omitted or unknown, the implementation uses **McCamy**.

---

## Example script

Run the shipped example to compare both algorithms side by side on sample colors:

```bash
php examples/Extractor/Temperature.php
```

---

## See also

- [Introduction to Extractors](/docs/extractors-analysis/introduction)
- [Extractors reference](/docs/references/extractors)
- `examples/Extractor/Temperature.php` in the repository

---
title: Extractors Reference
sidebar_position: 4
---

# Extractors Reference

Extractors compute a single **numeric or string value** from a `ColorInterface` instance (and optional parameters). They live in the `Negarity\Color\Extractor` namespace—there is **no** `Extractor\Analysis` sub-namespace in this library.

## Registry

Built-in extractors are **not** auto-registered. Register the ones you need on `ExtractorRegistry`, then resolve by name:

- `ExtractorRegistry::register(ExtractorInterface $extractor): void`
- `ExtractorRegistry::get(string $name): ExtractorInterface` — throws `ExtractorNotFoundException` if missing
- `ExtractorRegistry::has(string $name): bool`
- `ExtractorRegistry::unregister(string $name): void` — throws `ExtractorNotFoundException` if missing

You must also register color spaces (e.g. `ColorSpaceRegistry::registerBuiltIn()`) before creating or converting colors.

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\BrightnessExtractor;
use Negarity\Color\Extractor\ExtractorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
ExtractorRegistry::register(new BrightnessExtractor());

$color = Color::rgb(255, 100, 50);
$value = ExtractorRegistry::get('brightness')->extract($color);
$label = BrightnessExtractor::getLabelForValue($value);
```

## Built-in extractors

| Registry name | Class | Returns | Notes |
|---------------|-------|---------|--------|
| `temperature` | `TemperatureExtractor` | `float` (−1 cold … 1 warm) | McCamy (`original` / `refined`), Planckian UCS, Krystek — [guide](/docs/extractors-analysis/temperature) |
| `brightness` | `BrightnessExtractor` | `float` (0–100) | LCh L (default); RGB heuristics, Rec. 601/709, Lab L*, CIECAM02/16 — [guide](/docs/extractors-analysis/brightness) |
| `luminance` | `LuminanceExtractor` | `float` (0–100) | CIE XYZ Y — [guide](/docs/extractors-analysis/luminance) |
| `saturation` | `SaturationExtractor` | `float` (0–100) | HSV (default), HSL — [guide](/docs/extractors-analysis/saturation) |
| `chroma` | `ChromaExtractor` | `float` (0–100) | OKLCH (default), CIE Lab, CIE Luv — [guide](/docs/extractors-analysis/chroma) |
| `perceived_weight` | `PerceivedWeightExtractor` | `float` (0–100) | Linear (default), brightness × chroma — [guide](/docs/extractors-analysis/perceived-weight) |
| `vibrancy` | `VibrancyExtractor` | `float` (0–100) | Midtone chroma (default), Gaussian index — [guide](/docs/extractors-analysis/vibrancy) |
| `contrast` | `ContrastExtractor` | `float` | WCAG (default), Michelson, Weber, RMS, ΔE76 — [guide](/docs/extractors-analysis/contrast) |

Each built-in class provides **`public static function getLabelForValue(float|string $value): string`** for human-readable labels (WCAG bands for contrast, buckets for brightness, etc.).

### Temperature extractor parameters

See the **[Temperature](/docs/extractors-analysis/temperature)** guide for step-by-step pipelines (including **McCamy refined** formulas).

`TemperatureExtractor::extract($color, $params)` accepts an optional array with:

- **`algorithm`** (string, default: `mccamy`):
  - `mccamy` — McCamy cubic CCT from CIE 1931 (x, y); use **`version`** for original vs refined (`TemperatureExtractor::ALGORITHM_MCCAMY`)
  - `nearestPlanckianUcs1960` — nearest point on the Planckian locus in CIE 1960 UCS (`TemperatureExtractor::ALGORITHM_NEAREST_PLANCKIAN_UCS1960`). Aliases: `ucs1960`, `brute`, `planckian_locus`, …
  - `krystek1985` — Krystek (1985) rational *u,v(T)* + iterative inverse [1000, 15000] K (`TemperatureExtractor::ALGORITHM_KRYSTEK1985`). Aliases: `krystek`, …
- **`version`** (string, default: `original`) — **McCamy only** (`algorithm` = `mccamy`):
  - `original` — McCamy **1992** cubic: −449·n³ + 3525·n² − 6823.3·n + 5520.33; clamp 1k–25k K (`TemperatureExtractor::VERSION_ORIGINAL`)
  - `refined` — updated cubic: −437·n³ + 3601·n² − 6861·n + 5514.31; clamp 1k–250k K (`TemperatureExtractor::VERSION_REFINED`). Aliases: `updated`, `current`

Both McCamy versions **fall back** to nearest Planckian UCS when chromaticity is farther than **0.01** (u,v) from the locus.

`TemperatureExtractor::getVersionLabel($algorithm, $version)` returns UI labels (`Original (1992)`, `Refined`, …).

```php
// McCamy refined
$refined = ExtractorRegistry::get('temperature')->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY,
    'version' => TemperatureExtractor::VERSION_REFINED,
]);

// McCamy original (1992) — same as omitting version
$original = ExtractorRegistry::get('temperature')->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY,
    'version' => TemperatureExtractor::VERSION_ORIGINAL,
]);
```

### Saturation extractor parameters

See the **[Saturation](/docs/extractors-analysis/saturation)** guide for formulas.

`SaturationExtractor::extract($color, $params)` accepts an optional array with:

- **`algorithm`** (string, default: `hsv`):
  - `hsv` — HSV *S* = (V − min) / V; Alvy Ray Smith, SIGGRAPH 1978 (`SaturationExtractor::ALGORITHM_HSV`). Aliases: `alvy`, `smith`, `siggraph1978`
  - `hsl` — HSL *S* relative to lightness; Foley & van Dam (`SaturationExtractor::ALGORITHM_HSL`). Aliases: `foley`, `vandam`

Both algorithms read the **S** channel from `toHSV()` / `toHSL()` (0–100). For absolute colorfulness use [Chroma](/docs/extractors-analysis/chroma).

`SaturationExtractor::getAlgorithmLabel($algorithm)` returns UI labels.

```php
$hsv = ExtractorRegistry::get('saturation')->extract($color, [
    'algorithm' => SaturationExtractor::ALGORITHM_HSV,
]);

$hsl = ExtractorRegistry::get('saturation')->extract($color, [
    'algorithm' => SaturationExtractor::ALGORITHM_HSL,
]);
```

### Perceived weight extractor parameters

See the **[Perceived weight](/docs/extractors-analysis/perceived-weight)** guide for formulas.

`PerceivedWeightExtractor::extract($color, $params)` accepts an optional array with:

- **`algorithm`** (string, default: `brightnessChromaLinear`):
  - `brightnessChromaLinear` — 0.7·(100−L*) + 0.3·(C*/130)·100 (`PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_LINEAR`). Aliases: `linear`, `sum`, `additive`
  - `brightnessChromaMultiplication` — (100−L*) × (1 + k·C*/130) (`PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION`). Aliases: `multiplication`, `multiply`, `multiplicative`
- **`k`** (float, default: `0.5`) — chroma factor for the multiplication algorithm only

Both algorithms use LCh **L** and **c** via `toLCh()`. `PerceivedWeightExtractor::getAlgorithmLabel($algorithm)` returns UI labels.

```php
$linear = ExtractorRegistry::get('perceived_weight')->extract($color);

$multiplication = ExtractorRegistry::get('perceived_weight')->extract($color, [
    'algorithm' => PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION,
    'k' => 0.5,
]);
```

### Vibrancy extractor parameters

See the **[Vibrancy](/docs/extractors-analysis/vibrancy)** guide for formulas.

`VibrancyExtractor::extract($color, $params)` accepts an optional array with:

- **`algorithm`** (string, default: `midtoneChromaIndex`):
  - `midtoneChromaIndex` — C_norm × midPeak × 100; triangular envelope at L* = 50 (`VibrancyExtractor::ALGORITHM_MIDTONE_CHROMA_INDEX`). Aliases: `midtone`, `triangle`, `chroma_index`
  - `gaussianVibrancyIndex` — C_norm × exp(−(L*−μ)² / (2σ²)) × 100 (`VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX`). Aliases: `gaussian`, `gauss`, `normal`
- **`mu`** (float, default: `50`) — Gaussian centre μ (L* units)
- **`sigma`** (float, default: `25`) — Gaussian width σ (L* units)

`VibrancyExtractor::getAlgorithmLabel($algorithm)` returns UI labels.

```php
$midtone = ExtractorRegistry::get('vibrancy')->extract($color);

$gaussian = ExtractorRegistry::get('vibrancy')->extract($color, [
    'algorithm' => VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX,
    'mu' => 50,
    'sigma' => 25,
]);
```

### Brightness extractor parameters

See the **[Brightness](/docs/extractors-analysis/brightness)** guide for formulas.

`BrightnessExtractor::extract($color, $params)` accepts an optional array with:

- **`algorithm`** (string, default: `lch`):
  - `lch` — LCh **L** / perceptual lightness (`BrightnessExtractor::ALGORITHM_LCH`)
  - `average` — (R + G + B) / 3 on gamma-encoded RGB
  - `lightness` — (max + min) / 2 on RGB channels
  - `hsvValue` — HSV **V** = max(R,G,B); aliases: `value`, `max`, `hsb`
  - `rec601` — ITU-R BT.601 luma on 8-bit RGB
  - `rec709` — ITU-R BT.709 luma on 8-bit RGB
  - `cie1976Lab` — CIE 1976 L\*a\*b\* **L\***; aliases: `lab`, `cielab`
  - `ciecam02` — CIECAM02 lightness **J**
  - `ciecam16` — CIECAM16 lightness **J**
- **`L_A`** (float, optional) — adapting luminance (cd/m²) for CIECAM02/16
- **`Y_b`** (float, optional) — background **Y** factor for CIECAM02/16

`BrightnessExtractor::getAlgorithmLabel($algorithm)` returns UI labels.

```php
$lch = ExtractorRegistry::get('brightness')->extract($color);

$rec709 = ExtractorRegistry::get('brightness')->extract($color, [
    'algorithm' => BrightnessExtractor::ALGORITHM_REC709,
]);
```

### Chroma extractor parameters

See the **[Chroma](/docs/extractors-analysis/chroma)** guide for formulas and normalization.

`ChromaExtractor::extract($color, $params)` accepts an optional array with:

- **`algorithm`** (string, default: `oklch`):
  - `oklch` — OKLab/OKLCH *C* = √(a² + b²); normalize with divisor **0.4** (`ChromaExtractor::ALGORITHM_OKLCH`). Aliases: `oklab`, `ok`
  - `cie1976Lab` — CIE 1976 L\*a\*b\* *C\**; divisor **150** (`ChromaExtractor::ALGORITHM_CIE1976_LAB`). Aliases: `lab`, `cielab`, `cstar`
  - `cie1976Luv` — CIE 1976 L\*u\*v\* *C\*uv*; divisor **150** (`ChromaExtractor::ALGORITHM_CIE1976_LUV`). Aliases: `luv`, `cieluv`

`ChromaExtractor::getAlgorithmLabel($algorithm)` returns UI labels.

```php
$oklch = ExtractorRegistry::get('chroma')->extract($color, [
    'algorithm' => ChromaExtractor::ALGORITHM_OKLCH,
]);

$lab = ExtractorRegistry::get('chroma')->extract($color, [
    'algorithm' => ChromaExtractor::ALGORITHM_CIE1976_LAB,
]);
```

### Contrast extractor parameters

See the **[Contrast](/docs/extractors-analysis/contrast)** guide for formulas and white/black pairing.

`ContrastExtractor::extract($color, $params)` accepts:

- **Legacy:** `null`, `'white'`, `'black'`, or a `ColorInterface` → **WCAG** vs that reference
- **Array:**
  - **`algorithm`** (string, default: `wcagContrastRatio`):
    - `wcagContrastRatio` — WCAG 2.x ratio 1–21 (`ContrastExtractor::ALGORITHM_WCAG_CONTRAST_RATIO`)
    - `michelsonContrast` — (L_max−L_min)/(L_max+L_min)×100
    - `weberContrast` — (L_target−L_bg)/L_bg×100
    - `rmsContrast` — |L₁−L₂|/√(½(L₁²+L₂²))×100
    - `deltaE76` — CIE76 ΔE*ab in Lab
  - **`contrastWith`** / **`reference`**: `'white'`, `'black'`, or `ColorInterface`

`ContrastExtractor::getAlgorithmLabel($algorithm)`, `getDisplayRange($algorithm)`, and `getLabelForValue($value, $algorithm)` support UI and API responses.

```php
$wcagWhite = ExtractorRegistry::get('contrast')->extract($color, 'white');

$michelsonBlack = ExtractorRegistry::get('contrast')->extract($color, [
    'algorithm' => ContrastExtractor::ALGORITHM_MICHELSON_CONTRAST,
    'contrastWith' => 'black',
]);

$deltaE = ExtractorRegistry::get('contrast')->extract($color, [
    'algorithm' => ContrastExtractor::ALGORITHM_DELTA_E76,
    'contrastWith' => 'white',
]);
```

## `ExtractorInterface`

```php
interface ExtractorInterface
{
    public function getName(): string;

    public function extract(ColorInterface $color, mixed $params = null): float|string;
}
```

Implementations should also expose **`getLabelForValue()`** (static, on the concrete class) so UIs can show labels for extracted values. The return type of `extract()` is **`float|string`** only.

## Example (all built-ins)

See the library example `examples/Extractor/Extractors.php`—it registers every built-in extractor and prints value + label for sample colors.

## See also

- [Introduction to Extractors](/docs/extractors-analysis/introduction)
- [Temperature](/docs/extractors-analysis/temperature)
- [Brightness](/docs/extractors-analysis/brightness)
- [Luminance](/docs/extractors-analysis/luminance)
- [Saturation](/docs/extractors-analysis/saturation)
- [Chroma](/docs/extractors-analysis/chroma)
- [Perceived weight](/docs/extractors-analysis/perceived-weight)
- [Vibrancy](/docs/extractors-analysis/vibrancy)
- [Contrast](/docs/extractors-analysis/contrast)
- [Adding Extractors](/docs/extending/extractors)
- [Exceptions Reference](/docs/references/exceptions) — `ExtractorNotFoundException`

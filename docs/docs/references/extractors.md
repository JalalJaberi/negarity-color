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
| `brightness` | `BrightnessExtractor` | `float` (0–100) | LCh L (perceived lightness) |
| `saturation` | `SaturationExtractor` | `float` (0–100) | HSV (default), HSL — [guide](/docs/extractors-analysis/saturation) |
| `chroma` | `ChromaExtractor` | `float` (0–100) | OKLCH (default), CIE Lab, CIE Luv — [guide](/docs/extractors-analysis/chroma) |
| `perceived_weight` | `PerceivedWeightExtractor` | `float` (0–100) | Dark + saturated → heavier |
| `vibrancy` | `VibrancyExtractor` | `float` (0–100) | Mid-light + high chroma peaks |
| `contrast` | `ContrastExtractor` | `float` (1–21) | WCAG contrast vs another color |

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

`ContrastExtractor::extract($color, $params)`:

- `null` or `'white'` — contrast against white (default)
- `'black'` — contrast against black
- Any `ColorInterface` — contrast against that color

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
- [Adding Extractors](/docs/extending/extractors)
- [Exceptions Reference](/docs/references/exceptions) — `ExtractorNotFoundException`

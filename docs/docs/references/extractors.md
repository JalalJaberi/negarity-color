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
| `temperature` | `TemperatureExtractor` | `float` (−1 cold … 1 warm) | CCT via McCamy (default) or optional Planckian UCS search — [guide](/docs/extractors-analysis/temperature) and below |
| `brightness` | `BrightnessExtractor` | `float` (0–100) | LCh L (perceived lightness) |
| `saturation` | `SaturationExtractor` | `float` (0–100) | LCh chroma normalized |
| `chroma` | `ChromaExtractor` | `float` (0–100) | Neutral vs “colory” |
| `perceived_weight` | `PerceivedWeightExtractor` | `float` (0–100) | Dark + saturated → heavier |
| `vibrancy` | `VibrancyExtractor` | `float` (0–100) | Mid-light + high chroma peaks |
| `contrast` | `ContrastExtractor` | `float` (1–21) | WCAG contrast vs another color |

Each built-in class provides **`public static function getLabelForValue(float|string $value): string`** for human-readable labels (WCAG bands for contrast, buckets for brightness, etc.).

### Temperature extractor parameters

For a **step-by-step** explanation of chromaticity → Kelvin → signed value and both algorithms, see the **[Temperature](/docs/extractors-analysis/temperature)** guide.

`TemperatureExtractor::extract($color, $params)` accepts an optional array with:

- **`algorithm`** (string, default: `mccamy`):
  - `mccamy` — McCamy cubic CCT from CIE 1931 (x, y) (`TemperatureExtractor::ALGORITHM_MCCAMY`)
  - `nearestPlanckianUcs1960` — brute-force nearest point on the Planckian locus in CIE 1960 UCS (`TemperatureExtractor::ALGORITHM_NEAREST_PLANCKIAN_UCS1960`). Aliases: `ucs1960`, `brute`, `planckian_locus`, …
- **`version`** (string, default: `original`) — for **McCamy** only:
  - `original` — canonical 1992 cubic (`TemperatureExtractor::VERSION_ORIGINAL`)
  - `refined` — updated cubic coefficients (`TemperatureExtractor::VERSION_REFINED`)

Both McCamy versions **fall back** to nearest Planckian UCS when chromaticity is farther than **0.01** (u,v) from the locus (saturated primaries).

Use `TemperatureExtractor::getVersionLabel($algorithm, $version)` for display names in UIs.

```php
$signed = ExtractorRegistry::get('temperature')->extract($color, [
    'algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY,
    'version' => TemperatureExtractor::VERSION_REFINED,
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

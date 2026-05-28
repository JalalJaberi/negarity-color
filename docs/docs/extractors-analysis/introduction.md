---
title: Introduction to Extractors
sidebar_position: 1
---

# Introduction to Extractors

**Extractors** are small services that read a `ColorInterface` and return one **scalar** result: either a **`float`** or a **`string`** (for example a category slug). They are useful for theming, accessibility checks, UI copy (“warm”, “very dark”), and analytics.

## How they fit in the library

- Classes live under **`Negarity\Color\Extractor`**.
- They are looked up by name via **`ExtractorRegistry`** (same idea as `FilterRegistry` for filters).
- The extractor registry has **no** `registerBuiltIn()` helper—you **`register()`** each extractor instance you need.

Always call **`ColorSpaceRegistry::registerBuiltIn()`** (or register spaces yourself) before creating colors, same as elsewhere in the docs.

## Built-in extractors

| Name | What it measures |
|------|------------------|
| `temperature` | Warm vs cool (−1 … 1) from chromaticity — see [Temperature](/docs/extractors-analysis/temperature) |
| `brightness` | Perceived lightness 0–100 (LCh L) |
| `luminance` | Relative luminance 0–100 (CIE XYZ Y) — see [Luminance](/docs/extractors-analysis/luminance) |
| `saturation` | Vivid vs dull 0–100 (HSV default, HSL) — see [Saturation](/docs/extractors-analysis/saturation) |
| `chroma` | How “colored” vs neutral 0–100 — see [Chroma](/docs/extractors-analysis/chroma) |
| `perceived_weight` | Visual heaviness 0–100 |
| `vibrancy` | Dull vs vibrant 0–100 |
| `contrast` | WCAG contrast ratio vs white, black, or another color |

Each built-in extractor class implements **`ExtractorInterface`** and exposes **`getLabelForValue()`** so you can turn numbers into short labels.

## Quick example

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\ContrastExtractor;
use Negarity\Color\Extractor\ExtractorRegistry;
use Negarity\Color\Extractor\TemperatureExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
ExtractorRegistry::register(new TemperatureExtractor());
ExtractorRegistry::register(new ContrastExtractor());

$color = Color::rgb(30, 30, 180);

$temp = ExtractorRegistry::get('temperature')->extract($color);
echo TemperatureExtractor::getLabelForValue($temp); // e.g. "cool"

$ratio = ExtractorRegistry::get('contrast')->extract($color, 'white');
echo ContrastExtractor::getLabelForValue($ratio);   // e.g. "AA", "AAA"
```

## Custom extractors

Implement **`ExtractorInterface`**, add a **`getLabelForValue()`** helper on your class, then **`ExtractorRegistry::register(new YourExtractor())`**. See [Adding Extractors](/docs/extending/extractors).

## Reference

- [Temperature](/docs/extractors-analysis/temperature) — CCT: McCamy original & refined, Planckian UCS, Krystek
- [Saturation](/docs/extractors-analysis/saturation) — HSV (Smith), HSL (Foley & van Dam)
- [Chroma](/docs/extractors-analysis/chroma) — OKLCH, CIE Lab, CIE Luv
- [Luminance](/docs/extractors-analysis/luminance) — CIE XYZ Y (linear RGB)
- [Extractors Reference](/docs/references/extractors) — API details and parameter semantics

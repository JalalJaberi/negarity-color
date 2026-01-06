---
title: Extractors Reference
sidebar_position: 4
---

# Extractors Reference

Extractors are tools for analyzing and extracting information from colors. This reference covers the extractor system in Negarity Color.

## Available Extractors

The Negarity Color library includes extractors in the `Extractor/Analysis` namespace:

### Analysis Extractors

These extractors provide color analysis capabilities:

| Extractor | Class | Description |
|-----------|-------|-------------|
| **Contrast Analyzer** | `ContrastAnalyzer` | Analyzes color contrast ratios |
| **Delta E Analyzer** | `DeltaEAnalyzer` | Calculates perceptual color difference (ΔE) |
| **Luminance Analyzer** | `LuminanceAnalyzer` | Calculates relative luminance |

## Using Extractors

Extractors are typically used to analyze color properties:

```php
use Negarity\Color\Extractor\Analysis\LuminanceAnalyzer;
use Negarity\Color\Color;

$color = Color::rgb(255, 100, 50);
$analyzer = new LuminanceAnalyzer();
$luminance = $analyzer->analyze($color);
```

## Extractor Interface

All extractors implement the `ExtractorInterface`:

```php
interface ExtractorInterface
{
    public function extract(ColorInterface $color): mixed;
}
```

## Common Use Cases

### Color Contrast Analysis

```php
use Negarity\Color\Extractor\Analysis\ContrastAnalyzer;

$color1 = Color::rgb(255, 255, 255); // White
$color2 = Color::rgb(0, 0, 0);       // Black

$analyzer = new ContrastAnalyzer();
$contrast = $analyzer->getContrastRatio($color1, $color2);
// Returns contrast ratio (e.g., 21:1 for black/white)
```

### Color Difference Analysis

```php
use Negarity\Color\Extractor\Analysis\DeltaEAnalyzer;

$color1 = Color::rgb(255, 100, 50);
$color2 = Color::rgb(250, 105, 55);

$analyzer = new DeltaEAnalyzer();
$deltaE = $analyzer->calculate($color1, $color2);
// Returns perceptual color difference
```

### Luminance Analysis

```php
use Negarity\Color\Extractor\Analysis\LuminanceAnalyzer;

$color = Color::rgb(255, 100, 50);
$analyzer = new LuminanceAnalyzer();
$luminance = $analyzer->analyze($color);
// Returns relative luminance (0.0 to 1.0)
```

## Creating Custom Extractors

To create a custom extractor, implement the `ExtractorInterface`:

```php
use Negarity\Color\ColorInterface;
use Negarity\Color\Extractor\ExtractorInterface;

class MyCustomExtractor implements ExtractorInterface
{
    public function extract(ColorInterface $color): mixed
    {
        // Your extraction logic
        return $result;
    }
}
```

## See Also

- [Introduction to Extractors](/docs/extractors-analysis/introduction) - Overview of extractors
- [Adding Extractors](/docs/extending/extractors) - How to create custom extractors

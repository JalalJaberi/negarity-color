---
title: Adding Extractors
sidebar_position: 3
---

# Adding Extractors

This guide shows you how to create custom extractors for analyzing colors.

## Overview

Extractors analyze colors and extract information from them. To create a custom extractor:

1. Create a class implementing `ExtractorInterface`
2. Implement the `extract()` method
3. Use your extractor to analyze colors

## Step 1: Create the Extractor Interface

First, define the interface your extractor will implement:

```php
<?php

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

interface ExtractorInterface
{
    public function extract(ColorInterface $color): mixed;
}
```

## Step 2: Create Your Extractor Class

Create a class implementing the interface:

```php
<?php

namespace MyApp\ColorExtractor;

use Negarity\Color\ColorInterface;
use Negarity\Color\Extractor\ExtractorInterface;

class WarmthExtractor implements ExtractorInterface
{
    public function extract(ColorInterface $color): float
    {
        // Convert to RGB for analysis
        $rgb = $color->toRGB();
        
        // Calculate warmth (red/orange = warm, blue = cool)
        $r = $rgb->getR();
        $g = $rgb->getG();
        $b = $rgb->getB();
        
        // Simple warmth calculation
        $warmth = ($r - $b) / 255.0;
        
        return $warmth; // -1.0 (cool) to 1.0 (warm)
    }
}
```

## Step 3: Use Your Extractor

Use your custom extractor:

```php
use Negarity\Color\Color;
use MyApp\ColorExtractor\WarmthExtractor;

$color = Color::rgb(255, 100, 50);
$extractor = new WarmthExtractor();
$warmth = $extractor->extract($color);

if ($warmth > 0.5) {
    echo "Warm color";
} else if ($warmth < -0.5) {
    echo "Cool color";
} else {
    echo "Neutral color";
}
```

## Complete Examples

### Color Dominance Extractor

Extracts the dominant color channel:

```php
<?php

namespace MyApp\ColorExtractor;

use Negarity\Color\ColorInterface;
use Negarity\Color\Extractor\ExtractorInterface;

class DominanceExtractor implements ExtractorInterface
{
    public function extract(ColorInterface $color): string
    {
        $rgb = $color->toRGB();
        
        $r = $rgb->getR();
        $g = $rgb->getG();
        $b = $rgb->getB();
        
        $max = max($r, $g, $b);
        
        return match ($max) {
            $r => 'red',
            $g => 'green',
            $b => 'blue',
            default => 'neutral',
        };
    }
}
```

### Brightness Analyzer

Analyzes color brightness:

```php
<?php

namespace MyApp\ColorExtractor;

use Negarity\Color\ColorInterface;
use Negarity\Color\Extractor\ExtractorInterface;

class BrightnessAnalyzer implements ExtractorInterface
{
    public function extract(ColorInterface $color): array
    {
        $rgb = $color->toRGB();
        
        // Calculate relative luminance (WCAG formula)
        $r = $rgb->getR() / 255.0;
        $g = $rgb->getG() / 255.0;
        $b = $rgb->getB() / 255.0;
        
        $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);
        
        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        
        return [
            'luminance' => $luminance,
            'isLight' => $luminance > 0.5,
            'isDark' => $luminance < 0.5,
        ];
    }
}
```

### Color Harmony Extractor

Extracts complementary and analogous colors:

```php
<?php

namespace MyApp\ColorExtractor;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\Extractor\ExtractorInterface;

class HarmonyExtractor implements ExtractorInterface
{
    public function extract(ColorInterface $color): array
    {
        $hsl = $color->toHSL();
        $h = $hsl->getH();
        $s = $hsl->getS();
        $l = $hsl->getL();
        
        // Complementary (opposite on color wheel)
        $complementaryH = ($h + 180) % 360;
        $complementary = Color::hsl($complementaryH, $s, $l);
        
        // Analogous (adjacent colors)
        $analogous1 = Color::hsl(($h + 30) % 360, $s, $l);
        $analogous2 = Color::hsl(($h - 30 + 360) % 360, $s, $l);
        
        return [
            'complementary' => $complementary,
            'analogous' => [$analogous1, $analogous2],
        ];
    }
}
```

## Advanced: Parameterized Extractors

You can create extractors that accept parameters:

```php
<?php

namespace MyApp\ColorExtractor;

use Negarity\Color\ColorInterface;

class SimilarityExtractor
{
    public function extract(ColorInterface $color1, ColorInterface $color2): float
    {
        $rgb1 = $color1->toRGB();
        $rgb2 = $color2->toRGB();
        
        // Calculate Euclidean distance
        $dr = $rgb1->getR() - $rgb2->getR();
        $dg = $rgb1->getG() - $rgb2->getG();
        $db = $rgb1->getB() - $rgb2->getB();
        
        $distance = sqrt($dr * $dr + $dg * $dg + $db * $db);
        
        // Normalize to 0-1 (0 = identical, 1 = completely different)
        return $distance / 441.67; // Max distance in RGB space
    }
}
```

## Best Practices

- **Single Responsibility**: Each extractor should extract one type of information
- **Color Space Handling**: Convert to appropriate space for your calculations
- **Return Types**: Use clear, consistent return types
- **Error Handling**: Validate inputs and handle edge cases
- **Documentation**: Document what your extractor does and returns

## See Also

- [Extractors Reference](/docs/references/extractors) - Available extractors
- [Introduction to Extractors](/docs/extractors-analysis/introduction) - Overview

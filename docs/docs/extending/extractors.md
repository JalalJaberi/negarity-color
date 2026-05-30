---
title: Adding Extractors
sidebar_position: 3
---

# Adding Extractors

This guide shows how to implement **custom extractors** compatible with **`ExtractorRegistry`**.

## Requirements

1. Implement **`Negarity\Color\Extractor\ExtractorInterface`**.
2. **`getName()`** — unique registry key (e.g. `'warmth'`).
3. **`extract(ColorInterface $color, mixed $params = null): float|string`** — your computation; optional `$params` for parameterized extractors (same pattern as `ContrastExtractor`).
4. **`public static function getLabelForValue(float|string $value): string`** — human-readable label for UI (recommended; all built-ins provide this).

The interface only declares `getName()` and `extract()`; `getLabelForValue()` is a documented convention on concrete classes.

## Step 1: Implement the interface

```php
<?php

namespace MyApp\Color;

use Negarity\Color\ColorInterface;
use Negarity\Color\Extractor\ExtractorInterface;

final class WarmthExtractor implements ExtractorInterface
{
    public function getName(): string
    {
        return 'warmth';
    }

    public function extract(ColorInterface $color, mixed $params = null): float
    {
        $rgb = $color->toRGB();
        $r = $rgb->getChannel('r');
        $b = $rgb->getChannel('b');

        // Simple warm–cool score: −1 (cool) … 1 (warm)
        return (float) (($r - $b) / 255.0);
    }

    public static function getLabelForValue(float|string $value): string
    {
        $v = is_numeric($value) ? (float) $value : 0.0;
        if ($v > 0.35) {
            return 'warm';
        }
        if ($v < -0.35) {
            return 'cool';
        }
        return 'neutral';
    }
}
```

## Step 2: Register and use

```php
use Negarity\Color\Color;
use Negarity\Color\Extractor\ExtractorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;
use MyApp\Color\WarmthExtractor;

ColorSpaceRegistry::registerBuiltIn();
ExtractorRegistry::register(new WarmthExtractor());

$color = Color::rgb(255, 120, 40);
$score = ExtractorRegistry::get('warmth')->extract($color);
echo WarmthExtractor::getLabelForValue($score);
```

## Parameterized extractor

Use the second argument of `extract()` for options (compare `ContrastExtractor`, `BrightnessExtractor`, `SaturationExtractor`, `ChromaExtractor`):

```php
public function extract(ColorInterface $color, mixed $params = null): float
{
    $algorithm = is_array($params) && isset($params['algorithm'])
        ? (string) $params['algorithm']
        : 'default';
    // ...
    return $result;
}
```

Call site: `ExtractorRegistry::get('brightness')->extract($color, ['algorithm' => 'rec709']);`

## Tips

- **Color space**: Convert with `$color->toRGB()`, `toLCh()`, etc., depending on what you need.
- **Return type**: Only **`float`** or **`string`** — not arrays or objects.
- **Registration**: There is no bulk “register all extractors”; register exactly what your app uses.
- **Errors**: Unknown names throw **`ExtractorNotFoundException`** (see [Exceptions](/docs/references/exceptions)).

## See also

- [Extractors Reference](/docs/references/extractors)
- [Introduction to Extractors](/docs/extractors-analysis/introduction)

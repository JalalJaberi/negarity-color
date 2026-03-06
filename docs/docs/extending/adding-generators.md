---
title: Adding Generators
sidebar_position: 5
---

# Adding Generators

This guide shows you how to create custom generators that produce related colors from a base color (e.g. complementary, triadic, or custom harmonies).

## Overview

To add a new generator:

1. Create a class implementing `GeneratorInterface`
2. Implement `getName()` and `apply()`
3. Register the generator with `GeneratorRegistry`
4. Use it as a method on color instances

## GeneratorInterface

```php
namespace Negarity\Color\Generator;

use Negarity\Color\ColorInterface;

interface GeneratorInterface
{
    /** Get the name used to invoke this generator (e.g. $color->complementary()). */
    public function getName(): string;

    /** Generate a color from the given color and optional value (e.g. method enum/string). */
    public function apply(ColorInterface $color, mixed $value = null): ColorInterface;
}
```

- **getName()** – Must return a unique string. This becomes the method name on the color (e.g. `complementary`, `triadic1`). Use a valid PHP method name (letters, numbers, underscores).
- **apply()** – Receives the base color and an optional `$value` (e.g. method enum, step index, or options array). Must return a `ColorInterface` instance.

## Step 1: Create Your Generator Class

Example: a generator that returns a color with hue shifted by a fixed amount.

```php
<?php

namespace MyApp\ColorGenerator;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\Generator\GeneratorInterface;

class HueShiftGenerator implements GeneratorInterface
{
    public function __construct(
        private readonly float $degrees
    ) {
    }

    public function getName(): string
    {
        return 'hue_shift_' . (int) $this->degrees;
    }

    public function apply(ColorInterface $color, mixed $value = null): ColorInterface
    {
        $hsl = $color->toHSL();
        $h = $hsl->getChannel('h');
        $s = $hsl->getChannel('s');
        $l = $hsl->getChannel('l');

        $newH = fmod($h + $this->degrees, 360.0);
        if ($newH < 0) {
            $newH += 360.0;
        }

        $illuminant = $color->getIlluminant();
        $observer = $color->getObserver();
        $shifted = new Color(HSL::class, ['h' => $newH, 's' => $s, 'l' => $l], $illuminant, $observer);

        return $shifted->to($color->getColorSpaceName());
    }
}
```

## Step 2: Register the Generator

Register with `GeneratorRegistry` (usually at bootstrap):

```php
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;
use MyApp\ColorGenerator\HueShiftGenerator;

ColorSpaceRegistry::registerBuiltIn();
GeneratorRegistry::register(new HueShiftGenerator(90));
```

## Step 3: Use the Generator

Once registered, the generator is available as a method on any color:

```php
use Negarity\Color\Color;

$color = Color::rgb(255, 0, 0);
$shifted = $color->hue_shift_90();
```

You can also call it via the registry:

```php
use Negarity\Color\Generator\GeneratorRegistry;

$generator = GeneratorRegistry::get('hue_shift_90');
$shifted = $generator->apply($color);
```

## Passing a Value (method, step, options)

The second argument to `apply()` is optional. Use it for:

- **Method selection** – e.g. `ComplementMethod::Artistic` or `'perceptual'`
- **Step index** – e.g. for monochromatic step 1, 2, 3
- **Options array** – e.g. `['method' => 'perceptual', 'count' => 5]`

Example with a string method:

```php
public function apply(ColorInterface $color, mixed $value = null): ColorInterface
{
    $method = $value === null ? 'perceptual' : (string) $value;
    // ...
}
```

Example with an options array:

```php
public function apply(ColorInterface $color, mixed $value = null): ColorInterface
{
    $options = is_array($value) ? $value : [];
    $count = (int) ($options['count'] ?? 4);
    $method = $options['method'] ?? 'perceptual';
    // ...
}
```

## Preserving Color Space and Alpha

To match built-in behavior, return the generated color in the same color space as the input when possible:

```php
$originalSpaceName = $color->getColorSpaceName();
$result = $shifted->to($originalSpaceName);
```

If the original color has alpha (e.g. RGBA, HSLA), copy alpha to the result:

```php
$originalSpaceClass = $color->getColorSpace();
if ($originalSpaceClass::supportAlphaChannel()) {
    $alphaChannel = $originalSpaceClass::getAlphaChannelName();
    if ($alphaChannel !== '' && in_array($alphaChannel, $color->getChannels(), true)) {
        $result = $result->with([$alphaChannel => $color->getChannel($alphaChannel)]);
    }
}
return $result;
```

## Complete Example: “Warmth” Generator

A generator that returns a warmer or cooler variant by shifting hue toward orange or blue:

```php
<?php

namespace MyApp\ColorGenerator;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\Generator\GeneratorInterface;

class WarmthGenerator implements GeneratorInterface
{
    public function __construct(
        private readonly string $name,
        private readonly float $hueShift
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function apply(ColorInterface $color, mixed $value = null): ColorInterface
    {
        $hsl = $color->toHSL();
        $h = fmod($hsl->getChannel('h') + $this->hueShift, 360.0);
        if ($h < 0) $h += 360.0;

        $warmer = new Color(
            HSL::class,
            ['h' => $h, 's' => $hsl->getChannel('s'), 'l' => $hsl->getChannel('l')],
            $color->getIlluminant(),
            $color->getObserver()
        );

        return $warmer->to($color->getColorSpaceName());
    }
}

// Register
GeneratorRegistry::register(new WarmthGenerator('warmer', 15));
GeneratorRegistry::register(new WarmthGenerator('cooler', -15));

// Use
$red = Color::rgb(255, 0, 0);
$warmer = $red->warmer();
$cooler = $red->cooler();
```

## See Also

- [Introduction to Generators](/docs/generators/introduction) – built-in generators and usage
- [Complementary Generator](/docs/generators/complementary) – example with method selection
- [Monochromatic Generators](/docs/generators/monochromatic) – example with step and options

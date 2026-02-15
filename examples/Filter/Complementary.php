<?php

/**
 * Complementary filter example.
 *
 * Shows how to register the filter and apply it with each method:
 * - Artistic:    complement in HSL (hue + 180°)
 * - Perceptual: complement in LCh (perceptually uniform)
 * - DisplayAccurate: complement in RGB (invert R, G, B)
 *
 * Run: php examples/Filter/Complementary.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Filter\ComplementMethod;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Unary\ComplementaryFilter;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
FilterRegistry::register(new ComplementaryFilter());

$red = Color::rgb(255, 0, 0);

echo "========== Complementary filter ==========" . PHP_EOL;
echo "Original: " . $red . PHP_EOL . PHP_EOL;

// Apply with each method (enum)
$artistic = $red->complementary(ComplementMethod::Artistic);
$perceptual = $red->complementary(ComplementMethod::Perceptual);
$displayAccurate = $red->complementary(ComplementMethod::DisplayAccurate);

echo "-------- RGB (red) --------" . PHP_EOL;
echo "Artistic:          " . $artistic . PHP_EOL;
echo "Perceptual:        " . $perceptual . PHP_EOL;
echo "Display-accurate:  " . $displayAccurate . PHP_EOL . PHP_EOL;

// Via FilterRegistry (same as $color->complementary($method))
$filter = FilterRegistry::get('complementary');
echo "Via FilterRegistry::get('complementary')->apply(\$red, 'artistic'): " . $filter->apply($red, 'artistic') . PHP_EOL . PHP_EOL;

// String method (e.g. from config or API) and default (no arg = perceptual)
echo "Via string 'perceptual': " . $red->complementary('perceptual') . PHP_EOL;
echo "Default (no arg):        " . $red->complementary() . PHP_EOL . PHP_EOL;

// Same filter in different color spaces (result is converted back to original space)
$hsl = $red->toHsl();
echo "-------- HSL (red in HSL) --------" . PHP_EOL;
echo "Original:   " . $hsl . PHP_EOL;
echo "Artistic:   " . $hsl->complementary(ComplementMethod::Artistic) . PHP_EOL;
echo "Perceptual: " . $hsl->complementary(ComplementMethod::Perceptual) . PHP_EOL . PHP_EOL;

$green = Color::rgb(0, 255, 0);
echo "-------- RGB (green) --------" . PHP_EOL;
echo "Original:   " . $green . PHP_EOL;
echo "Artistic:   " . $green->complementary(ComplementMethod::Artistic) . PHP_EOL;
echo "Perceptual: " . $green->complementary(ComplementMethod::Perceptual) . PHP_EOL;

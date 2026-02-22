<?php

/**
 * Analogous generator example.
 *
 * Shows how to register the analogous generators and apply them with each method:
 * - Artistic:    analogous in HSL (hue - 30° or + 30°)
 * - Perceptual: analogous in LCh (perceptually uniform)
 * - DisplayAccurate: analogous in HSV (hue - 30° or + 30°), then to RGB
 *
 * Run: php examples/Generator/Analogous.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Generator\AnalogousMethod;
use Negarity\Color\Generator\AnalogousGenerator;
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
GeneratorRegistry::register(new AnalogousGenerator(-30));  // analogous1: base - 30°
GeneratorRegistry::register(new AnalogousGenerator(30));   // analogous2: base + 30°

$red = Color::rgb(255, 0, 0);

echo "========== Analogous generator ==========" . PHP_EOL;
echo "Original: " . $red . PHP_EOL . PHP_EOL;

echo "-------- Perceptual (default) --------" . PHP_EOL;
echo "Analogous 1 (-30°): " . $red->analogous1() . PHP_EOL;
echo "Analogous 2 (+30°): " . $red->analogous2() . PHP_EOL . PHP_EOL;

echo "-------- Artistic (HSL) --------" . PHP_EOL;
echo "Analogous 1: " . $red->analogous1(AnalogousMethod::Artistic) . PHP_EOL;
echo "Analogous 2: " . $red->analogous2(AnalogousMethod::Artistic) . PHP_EOL . PHP_EOL;

echo "-------- Perceptual (LCh) --------" . PHP_EOL;
echo "Analogous 1: " . $red->analogous1(AnalogousMethod::Perceptual) . PHP_EOL;
echo "Analogous 2: " . $red->analogous2(AnalogousMethod::Perceptual) . PHP_EOL . PHP_EOL;

echo "-------- Display-accurate (HSV) --------" . PHP_EOL;
echo "Analogous 1: " . $red->analogous1(AnalogousMethod::DisplayAccurate) . PHP_EOL;
echo "Analogous 2: " . $red->analogous2(AnalogousMethod::DisplayAccurate) . PHP_EOL . PHP_EOL;

echo "Via string 'artistic':" . PHP_EOL;
echo "  analogous1: " . $red->analogous1('artistic') . PHP_EOL;
echo "  analogous2: " . $red->analogous2('artistic') . PHP_EOL;

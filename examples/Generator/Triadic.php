<?php

/**
 * Triadic generator example.
 *
 * Shows how to register the triadic generators and apply them with each method:
 * - Artistic:    triadic in HSL (hue + 120° or + 240°)
 * - Perceptual: triadic in LCh (perceptually uniform)
 * - DisplayAccurate: triadic in HSV (hue + 120° or + 240°), then to RGB
 *
 * Run: php examples/Generator/Triadic.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Generator\TriadicMethod;
use Negarity\Color\Generator\TriadicGenerator;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
FilterRegistry::register(new TriadicGenerator(120));  // triadic1: base + 120°
FilterRegistry::register(new TriadicGenerator(240));  // triadic2: base + 240°

$red = Color::rgb(255, 0, 0);

echo "========== Triadic generator ==========" . PHP_EOL;
echo "Original: " . $red . PHP_EOL . PHP_EOL;

echo "-------- Perceptual (default) --------" . PHP_EOL;
echo "Triadic 1 (+120°): " . $red->triadic1() . PHP_EOL;
echo "Triadic 2 (+240°): " . $red->triadic2() . PHP_EOL . PHP_EOL;

echo "-------- Artistic (HSL) --------" . PHP_EOL;
echo "Triadic 1: " . $red->triadic1(TriadicMethod::Artistic) . PHP_EOL;
echo "Triadic 2: " . $red->triadic2(TriadicMethod::Artistic) . PHP_EOL . PHP_EOL;

echo "-------- Perceptual (LCh) --------" . PHP_EOL;
echo "Triadic 1: " . $red->triadic1(TriadicMethod::Perceptual) . PHP_EOL;
echo "Triadic 2: " . $red->triadic2(TriadicMethod::Perceptual) . PHP_EOL . PHP_EOL;

echo "-------- Display-accurate (HSV) --------" . PHP_EOL;
echo "Triadic 1: " . $red->triadic1(TriadicMethod::DisplayAccurate) . PHP_EOL;
echo "Triadic 2: " . $red->triadic2(TriadicMethod::DisplayAccurate) . PHP_EOL . PHP_EOL;

// Via string method
echo "Via string 'artistic':" . PHP_EOL;
echo "  triadic1: " . $red->triadic1('artistic') . PHP_EOL;
echo "  triadic2: " . $red->triadic2('artistic') . PHP_EOL;

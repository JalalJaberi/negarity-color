<?php

/**
 * Split-Complementary generator example.
 *
 * Shows how to register the split-complementary generators and apply them with each method:
 * - Artistic:    split-complementary in HSL (hue + 150° or 210°)
 * - Perceptual: split-complementary in LCh (perceptually uniform)
 * - DisplayAccurate: split-complementary in HSV (hue + 150° or 210°), then to RGB
 *
 * Run: php examples/Generator/SplitComplementary.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Generator\SplitComplementaryMethod;
use Negarity\Color\Generator\SplitComplementaryGenerator;
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
GeneratorRegistry::register(new SplitComplementaryGenerator(150));  // split_complementary1: base + 150°
GeneratorRegistry::register(new SplitComplementaryGenerator(210));  // split_complementary2: base + 210°

$red = Color::rgb(255, 0, 0);

echo "========== Split-Complementary generator ==========" . PHP_EOL;
echo "Original: " . $red . PHP_EOL . PHP_EOL;

echo "-------- Perceptual (default) --------" . PHP_EOL;
echo "Split 1 (+150°): " . $red->split_complementary1() . PHP_EOL;
echo "Split 2 (+210°): " . $red->split_complementary2() . PHP_EOL . PHP_EOL;

echo "-------- Artistic (HSL) --------" . PHP_EOL;
echo "Split 1: " . $red->split_complementary1(SplitComplementaryMethod::Artistic) . PHP_EOL;
echo "Split 2: " . $red->split_complementary2(SplitComplementaryMethod::Artistic) . PHP_EOL . PHP_EOL;

echo "-------- Perceptual (LCh) --------" . PHP_EOL;
echo "Split 1: " . $red->split_complementary1(SplitComplementaryMethod::Perceptual) . PHP_EOL;
echo "Split 2: " . $red->split_complementary2(SplitComplementaryMethod::Perceptual) . PHP_EOL . PHP_EOL;

echo "Via string 'artistic':" . PHP_EOL;
echo "  split_complementary1: " . $red->split_complementary1('artistic') . PHP_EOL;
echo "  split_complementary2: " . $red->split_complementary2('artistic') . PHP_EOL;

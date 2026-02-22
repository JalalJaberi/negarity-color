<?php

/**
 * Tetradic generator example.
 *
 * Shows how to register the tetradic generators and apply them with each type:
 * - Square:    base + 90°, + 180°, + 270° (four colors 90° apart on the wheel)
 * - Rectangle: base + 30°, + 180°, + 210° (rectangular tetradic, two pairs of complements)
 *
 * Hue shift is applied in LCh (perceptually uniform). Default is Square.
 *
 * Run: php examples/Generator/Tetradic.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Generator\TetradicMethod;
use Negarity\Color\Generator\TetradicGenerator;
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
GeneratorRegistry::register(new TetradicGenerator(90, 30));   // tetradic1: square +90°, rectangle +30°
GeneratorRegistry::register(new TetradicGenerator(180, 180));  // tetradic2: +180° (same for both)
GeneratorRegistry::register(new TetradicGenerator(270, 210));  // tetradic3: square +270°, rectangle +210°

$red = Color::rgb(255, 0, 0);

echo "========== Tetradic generator ==========" . PHP_EOL;
echo "Original: " . $red . PHP_EOL . PHP_EOL;

echo "-------- Square (default): 90°, 180°, 270° --------" . PHP_EOL;
echo "Tetradic 1 (+90°):  " . $red->tetradic1() . PHP_EOL;
echo "Tetradic 2 (+180°): " . $red->tetradic2() . PHP_EOL;
echo "Tetradic 3 (+270°): " . $red->tetradic3() . PHP_EOL . PHP_EOL;

echo "-------- Square (explicit) --------" . PHP_EOL;
echo "Tetradic 1: " . $red->tetradic1(TetradicMethod::Square) . PHP_EOL;
echo "Tetradic 2: " . $red->tetradic2(TetradicMethod::Square) . PHP_EOL;
echo "Tetradic 3: " . $red->tetradic3(TetradicMethod::Square) . PHP_EOL . PHP_EOL;

echo "-------- Rectangle: 30°, 180°, 210° --------" . PHP_EOL;
echo "Tetradic 1 (+30°):  " . $red->tetradic1(TetradicMethod::Rectangle) . PHP_EOL;
echo "Tetradic 2 (+180°): " . $red->tetradic2(TetradicMethod::Rectangle) . PHP_EOL;
echo "Tetradic 3 (+210°): " . $red->tetradic3(TetradicMethod::Rectangle) . PHP_EOL . PHP_EOL;

echo "Via string 'rectangle':" . PHP_EOL;
echo "  tetradic1: " . $red->tetradic1('rectangle') . PHP_EOL;
echo "  tetradic2: " . $red->tetradic2('rectangle') . PHP_EOL;
echo "  tetradic3: " . $red->tetradic3('rectangle') . PHP_EOL;

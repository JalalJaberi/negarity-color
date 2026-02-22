<?php

/**
 * Monochromatic generator example (Shades, Tints, Tones).
 *
 * Shows how to register the monochromatic generators and apply them:
 * - Shades: darken (add black) – L reduced in steps
 * - Tints:  lighten (add white) – L increased in steps
 * - Tones:  desaturate (add gray) – S/C reduced in steps
 *
 * Methods: Artistic (HSL), Perceptual (LCh), DisplayAccurate (HSV).
 * Pass method and count via string or array: e.g. 'perceptual' or ['method' => 'perceptual', 'count' => 4].
 * Default count is 4 (base + 3 derived colors).
 *
 * Run: php examples/Generator/Monochromatic.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Generator\MonochromaticMethod;
use Negarity\Color\Generator\MonochromaticShadeGenerator;
use Negarity\Color\Generator\MonochromaticTintGenerator;
use Negarity\Color\Generator\MonochromaticToneGenerator;
use Negarity\Color\Generator\GeneratorRegistry;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();
// Register steps 1–9 for each variant (supports up to 10 colors per variant)
for ($i = 1; $i <= 9; $i++) {
    GeneratorRegistry::register(new MonochromaticShadeGenerator($i));
    GeneratorRegistry::register(new MonochromaticTintGenerator($i));
    GeneratorRegistry::register(new MonochromaticToneGenerator($i));
}

$red = Color::rgb(255, 0, 0);

echo "========== Monochromatic generators (count=4 default) ==========" . PHP_EOL;
echo "Original: " . $red . PHP_EOL . PHP_EOL;

$value = ['method' => 'perceptual', 'count' => 4];

echo "-------- Shades (darken) – Perceptual --------" . PHP_EOL;
echo "Base:     " . $red . PHP_EOL;
echo "Shade 1:  " . $red->mono_shade_1($value) . PHP_EOL;
echo "Shade 2:  " . $red->mono_shade_2($value) . PHP_EOL;
echo "Shade 3:  " . $red->mono_shade_3($value) . PHP_EOL . PHP_EOL;

echo "-------- Tints (lighten) – Perceptual --------" . PHP_EOL;
echo "Base:     " . $red . PHP_EOL;
echo "Tint 1:   " . $red->mono_tint_1($value) . PHP_EOL;
echo "Tint 2:   " . $red->mono_tint_2($value) . PHP_EOL;
echo "Tint 3:   " . $red->mono_tint_3($value) . PHP_EOL . PHP_EOL;

echo "-------- Tones (desaturate) – Perceptual --------" . PHP_EOL;
echo "Base:     " . $red . PHP_EOL;
echo "Tone 1:   " . $red->mono_tone_1($value) . PHP_EOL;
echo "Tone 2:   " . $red->mono_tone_2($value) . PHP_EOL;
echo "Tone 3:   " . $red->mono_tone_3($value) . PHP_EOL . PHP_EOL;

echo "-------- Shades – Artistic (HSL) --------" . PHP_EOL;
echo "Shade 1: " . $red->mono_shade_1(MonochromaticMethod::Artistic) . PHP_EOL;
echo "Shade 2: " . $red->mono_shade_2(MonochromaticMethod::Artistic) . PHP_EOL . PHP_EOL;

echo "-------- Tints – via string method, count=5 --------" . PHP_EOL;
$value5 = ['method' => 'perceptual', 'count' => 5];
echo "Tint 1: " . $red->mono_tint_1($value5) . PHP_EOL;
echo "Tint 2: " . $red->mono_tint_2($value5) . PHP_EOL;
echo "Tint 3: " . $red->mono_tint_3($value5) . PHP_EOL;
echo "Tint 4: " . $red->mono_tint_4($value5) . PHP_EOL;

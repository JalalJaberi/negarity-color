<?php

/**
 * Luminance extractor: CIE XYZ Y (linear RGB luminance).
 *
 * Run: php examples/Extractor/Luminance.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Extractor\LuminanceExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();

$extractor = new LuminanceExtractor();

$samples = [
    'black'  => Color::rgb(0, 0, 0),
    'white'  => Color::rgb(255, 255, 255),
    'gray50' => Color::rgb(128, 128, 128),
    'red'    => Color::rgb(255, 0, 0),
    'green'  => Color::rgb(0, 255, 0),
    'blue'   => Color::rgb(0, 0, 255),
    'yellow' => Color::rgb(255, 255, 0),
];

echo '========== LuminanceExtractor (CIE XYZ Y, 0–100) ==========' . PHP_EOL;
echo PHP_EOL;
echo 'Y from linear sRGB: 0.2126729·R + 0.7151522·G + 0.0721750·B (D65, same as Rec. 709).' . PHP_EOL;
echo PHP_EOL;

printf("%-10s | %10s | %12s | %8s\n", 'Sample', 'Luminance', 'Label', 'XYZ Y');
echo str_repeat('-', 48) . PHP_EOL;

foreach ($samples as $name => $color) {
    $value = $extractor->extract($color);
    $xyzY = $color->toXYZ()->getChannel('y');

    printf(
        "%-10s | %10.2f | %12s | %8.2f\n",
        $name,
        $value,
        LuminanceExtractor::getLabelForValue($value),
        $xyzY
    );
}

echo PHP_EOL;
echo 'Note: Luminance (XYZ Y) is physical; Brightness extractor uses LCh L (perceptual).' . PHP_EOL;

<?php

/**
 * Saturation extractor: HSV (default), HSL.
 *
 * Run: php examples/Extractor/Saturation.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Extractor\SaturationExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();

$extractor = new SaturationExtractor();

$samples = [
    'white'      => Color::rgb(255, 255, 255),
    'gray50'     => Color::rgb(128, 128, 128),
    'red'        => Color::rgb(255, 0, 0),
    'lime'       => Color::rgb(0, 255, 0),
    'blue'       => Color::rgb(0, 0, 255),
    'pastelPink' => Color::rgb(255, 200, 210),
    'darkRed'    => Color::rgb(128, 0, 0),
];

echo '========== SaturationExtractor: HSV, HSL ==========' . PHP_EOL;
echo PHP_EOL;
echo 'Scale: 0 (achromatic) … 100 (fully saturated). Default algorithm: hsv.' . PHP_EOL;
echo PHP_EOL;

printf(
    "%-14s | %-10s | %-10s\n",
    'Sample',
    'HSV S',
    'HSL S'
);
echo str_repeat('-', 40) . PHP_EOL;

foreach ($samples as $name => $color) {
    $hsv = $extractor->extract($color, ['algorithm' => SaturationExtractor::ALGORITHM_HSV]);
    $hsl = $extractor->extract($color, ['algorithm' => SaturationExtractor::ALGORITHM_HSL]);

    printf(
        "%-14s | %10.2f | %10.2f\n",
        $name,
        $hsv,
        $hsl
    );
}

echo PHP_EOL;
echo 'Algorithm labels:' . PHP_EOL;
foreach ([
    SaturationExtractor::ALGORITHM_HSV,
    SaturationExtractor::ALGORITHM_HSL,
] as $algo) {
    echo '  ' . $algo . ' → ' . SaturationExtractor::getAlgorithmLabel($algo) . PHP_EOL;
}

echo PHP_EOL;
echo 'Default (null params) uses HSV.' . PHP_EOL;
$default = $extractor->extract($samples['red'], null);
echo 'extract(red, null) = ' . number_format($default, 2) . ' → '
    . SaturationExtractor::getLabelForValue($default) . PHP_EOL;

echo PHP_EOL;
$hsvColor = $samples['red']->toHSV();
$hslColor = $samples['red']->toHSL();
echo 'HSV s for red: ' . round($hsvColor->getChannel('s'), 2) . PHP_EOL;
echo 'HSL s for red: ' . round($hslColor->getChannel('s'), 2) . PHP_EOL;

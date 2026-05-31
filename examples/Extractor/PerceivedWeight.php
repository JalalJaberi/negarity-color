<?php

/**
 * Perceived weight extractor: linear (default), brightness × chroma multiplication.
 *
 * Run: php examples/Extractor/PerceivedWeight.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Extractor\PerceivedWeightExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();

$extractor = new PerceivedWeightExtractor();

$samples = [
    'white'    => Color::rgb(255, 255, 255),
    'gray50'   => Color::rgb(128, 128, 128),
    'black'    => Color::rgb(0, 0, 0),
    'red'      => Color::rgb(255, 0, 0),
    'darkRed'  => Color::rgb(128, 0, 0),
    'pastelPink' => Color::rgb(255, 200, 210),
    'navy'     => Color::rgb(0, 0, 128),
];

echo '========== PerceivedWeightExtractor ==========' . PHP_EOL;
echo PHP_EOL;
echo 'Scale: 0 (light) … 100 (heavy). Default algorithm: brightnessChromaLinear.' . PHP_EOL;
echo PHP_EOL;

printf(
    "%-14s | %-10s | %-14s\n",
    'Sample',
    'Linear',
    'Multiplication'
);
echo str_repeat('-', 44) . PHP_EOL;

foreach ($samples as $name => $color) {
    $linear = $extractor->extract($color, [
        'algorithm' => PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_LINEAR,
    ]);
    $mult = $extractor->extract($color, [
        'algorithm' => PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION,
    ]);

    printf(
        "%-14s | %10.2f | %14.2f\n",
        $name,
        $linear,
        $mult
    );
}

echo PHP_EOL;
echo 'Algorithm labels:' . PHP_EOL;
foreach ([
    PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_LINEAR,
    PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION,
] as $algo) {
    echo '  ' . $algo . ' → ' . PerceivedWeightExtractor::getAlgorithmLabel($algo) . PHP_EOL;
}

echo PHP_EOL;
$default = $extractor->extract($samples['red'], null);
echo 'extract(red, null) = ' . number_format($default, 2) . ' → '
    . PerceivedWeightExtractor::getLabelForValue($default) . PHP_EOL;

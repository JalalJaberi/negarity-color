<?php

/**
 * Vibrancy extractor: midtone chroma index (default), Gaussian vibrancy index.
 *
 * Run: php examples/Extractor/Vibrancy.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Extractor\VibrancyExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();

$extractor = new VibrancyExtractor();

$samples = [
    'white'      => Color::rgb(255, 255, 255),
    'gray50'     => Color::rgb(128, 128, 128),
    'black'      => Color::rgb(0, 0, 0),
    'red'        => Color::rgb(255, 0, 0),
    'lime'       => Color::rgb(0, 255, 0),
    'pastelPink' => Color::rgb(255, 200, 210),
    'navy'       => Color::rgb(0, 0, 128),
];

echo '========== VibrancyExtractor ==========' . PHP_EOL;
echo PHP_EOL;
echo 'Scale: 0 (dull) … 100 (vibrant). Default algorithm: midtoneChromaIndex.' . PHP_EOL;
echo PHP_EOL;

printf(
    "%-14s | %-10s | %-10s\n",
    'Sample',
    'Midtone',
    'Gaussian'
);
echo str_repeat('-', 40) . PHP_EOL;

foreach ($samples as $name => $color) {
    $midtone = $extractor->extract($color, [
        'algorithm' => VibrancyExtractor::ALGORITHM_MIDTONE_CHROMA_INDEX,
    ]);
    $gaussian = $extractor->extract($color, [
        'algorithm' => VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX,
    ]);

    printf(
        "%-14s | %10.2f | %10.2f\n",
        $name,
        $midtone,
        $gaussian
    );
}

echo PHP_EOL;
echo 'Algorithm labels:' . PHP_EOL;
foreach ([
    VibrancyExtractor::ALGORITHM_MIDTONE_CHROMA_INDEX,
    VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX,
] as $algo) {
    echo '  ' . $algo . ' → ' . VibrancyExtractor::getAlgorithmLabel($algo) . PHP_EOL;
}

echo PHP_EOL;
$default = $extractor->extract($samples['lime'], null);
echo 'extract(lime, null) = ' . number_format($default, 2) . ' → '
    . VibrancyExtractor::getLabelForValue($default) . PHP_EOL;

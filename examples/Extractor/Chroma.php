<?php

/**
 * Chroma extractor: OKLCH (default), CIE 1976 Lab, CIE 1976 Luv.
 *
 * Run: php examples/Extractor/Chroma.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Extractor\ChromaExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();

$extractor = new ChromaExtractor();

$samples = [
    'white'      => Color::rgb(255, 255, 255),
    'gray50'     => Color::rgb(128, 128, 128),
    'red'        => Color::rgb(255, 0, 0),
    'lime'       => Color::rgb(0, 255, 0),
    'blue'       => Color::rgb(0, 0, 255),
    'pastelPink' => Color::rgb(255, 200, 210),
];

echo '========== ChromaExtractor: OKLCH, CIE Lab, CIE Luv ==========' . PHP_EOL;
echo PHP_EOL;
echo 'Scale: 0 (neutral) … 100 (high chroma). Default algorithm: oklch.' . PHP_EOL;
echo PHP_EOL;

printf(
    "%-14s | %-10s | %-10s | %-10s\n",
    'Sample',
    'OKLCH',
    'Lab C*',
    'Luv C*'
);
echo str_repeat('-', 54) . PHP_EOL;

foreach ($samples as $name => $color) {
    $oklch = $extractor->extract($color, ['algorithm' => ChromaExtractor::ALGORITHM_OKLCH]);
    $lab = $extractor->extract($color, ['algorithm' => ChromaExtractor::ALGORITHM_CIE1976_LAB]);
    $luv = $extractor->extract($color, ['algorithm' => ChromaExtractor::ALGORITHM_CIE1976_LUV]);

    printf(
        "%-14s | %10.2f | %10.2f | %10.2f\n",
        $name,
        $oklch,
        $lab,
        $luv
    );
}

echo PHP_EOL;
echo 'Algorithm labels:' . PHP_EOL;
foreach ([
    ChromaExtractor::ALGORITHM_OKLCH,
    ChromaExtractor::ALGORITHM_CIE1976_LAB,
    ChromaExtractor::ALGORITHM_CIE1976_LUV,
] as $algo) {
    echo '  ' . $algo . ' → ' . ChromaExtractor::getAlgorithmLabel($algo) . PHP_EOL;
}

echo PHP_EOL;
echo 'Default (null params) uses OKLCH.' . PHP_EOL;
$default = $extractor->extract($samples['red'], null);
echo 'extract(red, null) = ' . number_format($default, 2) . ' → '
    . ChromaExtractor::getLabelForValue($default) . PHP_EOL;

echo PHP_EOL;
$oklab = $samples['red']->toOklab();
echo 'OKLab a,b for red: a=' . round($oklab->getChannel('a'), 4)
    . ', b=' . round($oklab->getChannel('b'), 4) . PHP_EOL;
$oklchColor = $samples['red']->toOklch();
echo 'Oklch c for red: ' . round($oklchColor->getChannel('c'), 4) . PHP_EOL;

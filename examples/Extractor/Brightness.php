<?php

/**
 * Brightness extractor: LCh (default), RGB heuristics, Rec. 601/709, Lab L*, CIECAM02/16.
 *
 * Run: php examples/Extractor/Brightness.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Extractor\BrightnessExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();

$extractor = new BrightnessExtractor();

$samples = [
    'black'  => Color::rgb(0, 0, 0),
    'gray50' => Color::rgb(128, 128, 128),
    'white'  => Color::rgb(255, 255, 255),
    'red'    => Color::rgb(255, 0, 0),
    'yellow' => Color::rgb(255, 255, 0),
    'blue'   => Color::rgb(0, 0, 255),
];

$algorithms = [
    BrightnessExtractor::ALGORITHM_LCH,
    BrightnessExtractor::ALGORITHM_AVERAGE,
    BrightnessExtractor::ALGORITHM_LIGHTNESS,
    BrightnessExtractor::ALGORITHM_HSV_VALUE,
    BrightnessExtractor::ALGORITHM_REC601,
    BrightnessExtractor::ALGORITHM_REC709,
    BrightnessExtractor::ALGORITHM_CIE1976_LAB,
    BrightnessExtractor::ALGORITHM_CIECAM02,
    BrightnessExtractor::ALGORITHM_CIECAM16,
];

echo '========== BrightnessExtractor ==========' . PHP_EOL;
echo PHP_EOL;
echo 'Scale: 0 (dark) … 100 (light). Default algorithm: lch (LCh L).' . PHP_EOL;
echo PHP_EOL;

$header = sprintf('%-10s', 'Sample');
foreach ($algorithms as $algo) {
    $header .= ' | ' . sprintf('%-8s', $algo);
}
echo $header . PHP_EOL;
echo str_repeat('-', strlen($header)) . PHP_EOL;

foreach ($samples as $name => $color) {
    $row = sprintf('%-10s', $name);
    foreach ($algorithms as $algo) {
        $value = $extractor->extract($color, ['algorithm' => $algo]);
        $row .= ' | ' . sprintf('%8.2f', $value);
    }
    echo $row . PHP_EOL;
}

echo PHP_EOL;
echo 'Algorithm labels:' . PHP_EOL;
foreach ($algorithms as $algo) {
    echo '  ' . $algo . ' → ' . BrightnessExtractor::getAlgorithmLabel($algo) . PHP_EOL;
}

echo PHP_EOL;
$default = $extractor->extract($samples['red'], null);
echo 'extract(red, null) = ' . number_format($default, 2) . ' → '
    . BrightnessExtractor::getLabelForValue($default) . PHP_EOL;

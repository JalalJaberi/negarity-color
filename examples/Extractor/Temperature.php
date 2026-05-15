<?php

/**
 * Temperature extractor: compare McCamy (default) vs nearest Planckian search in CIE 1960 UCS.
 *
 * Run: php examples/Extractor/Temperature.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Extractor\TemperatureExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();

$extractor = new TemperatureExtractor();

$samples = [
    'white'        => Color::rgb(255, 255, 255),
    'warm_white'   => Color::rgb(255, 244, 230),
    'cool_white'   => Color::rgb(230, 240, 255),
    'red'          => Color::rgb(255, 0, 0),
    'blue'         => Color::rgb(0, 0, 255),
    'lime'         => Color::rgb(0, 255, 0),
    'gray50'       => Color::rgb(128, 128, 128),
];

echo '========== TemperatureExtractor: McCamy vs nearestPlanckianUcs1960 ==========' . PHP_EOL;
echo PHP_EOL;
echo 'Signed scale: −1 (cold) … +1 (warm). Labels apply to the signed value.' . PHP_EOL;
echo PHP_EOL;

printf(
    "%-14s | %-18s | %-18s | %-18s | %-18s\n",
    'Sample',
    'McCamy (signed)',
    'McCamy label',
    'UCS1960 (signed)',
    'UCS1960 label'
);
echo str_repeat('-', 100) . PHP_EOL;

foreach ($samples as $name => $color) {
    $mccamy = $extractor->extract($color, [
        'algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY,
    ]);
    $ucs = $extractor->extract($color, [
        'algorithm' => TemperatureExtractor::ALGORITHM_NEAREST_PLANCKIAN_UCS1960,
    ]);

    printf(
        "%-14s | %18s | %-18s | %18s | %-18s\n",
        $name,
        number_format($mccamy, 4),
        TemperatureExtractor::getLabelForValue($mccamy),
        number_format($ucs, 4),
        TemperatureExtractor::getLabelForValue($ucs)
    );
}

echo PHP_EOL;
echo 'Default parameters (null) use McCamy — same as algorithm => mccamy.' . PHP_EOL;
$mccamyDefault = $extractor->extract($samples['white'], null);
echo 'Example: extract(white, null) = ' . number_format($mccamyDefault, 4) . PHP_EOL;

<?php

/**
 * Temperature extractor: McCamy versions + nearest Planckian search in CIE 1960 UCS.
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

$mccamyVersions = [
    TemperatureExtractor::VERSION_ORIGINAL => 'McCamy original (1992)',
    TemperatureExtractor::VERSION_REFINED => 'McCamy refined',
];

echo '========== TemperatureExtractor: McCamy versions + UCS1960 ==========' . PHP_EOL;
echo PHP_EOL;
echo 'Signed scale: −1 (cold) … +1 (warm). Default version when omitted: original.' . PHP_EOL;
echo PHP_EOL;

printf(
    "%-14s | %-16s | %-16s | %-16s\n",
    'Sample',
    'Original',
    'Refined',
    'UCS1960'
);
echo str_repeat('-', 70) . PHP_EOL;

foreach ($samples as $name => $color) {
    $original = $extractor->extract($color, [
        'algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY,
        'version' => TemperatureExtractor::VERSION_ORIGINAL,
    ]);
    $refined = $extractor->extract($color, [
        'algorithm' => TemperatureExtractor::ALGORITHM_MCCAMY,
        'version' => TemperatureExtractor::VERSION_REFINED,
    ]);
    $ucs = $extractor->extract($color, [
        'algorithm' => TemperatureExtractor::ALGORITHM_NEAREST_PLANCKIAN_UCS1960,
    ]);

    printf(
        "%-14s | %16s | %16s | %16s\n",
        $name,
        number_format($original, 4),
        number_format($refined, 4),
        number_format($ucs, 4)
    );
}

echo PHP_EOL;
echo 'Version labels (McCamy):' . PHP_EOL;
foreach ($mccamyVersions as $version => $title) {
    echo '  ' . $version . ' → ' . TemperatureExtractor::getVersionLabel(
        TemperatureExtractor::ALGORITHM_MCCAMY,
        $version
    ) . ' (' . $title . ')' . PHP_EOL;
}

echo PHP_EOL;
echo 'Default parameters (null) use McCamy + version original.' . PHP_EOL;
$default = $extractor->extract($samples['white'], null);
echo 'extract(white, null) = ' . number_format($default, 4) . PHP_EOL;

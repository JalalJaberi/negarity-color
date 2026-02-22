<?php

/**
 * Test all extractors: runs each extractor on a set of colors and shows value + label.
 *
 * Run: php examples/Extractor/all.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Extractor\BrightnessExtractor;
use Negarity\Color\Extractor\ChromaExtractor;
use Negarity\Color\Extractor\ContrastExtractor;
use Negarity\Color\Extractor\ExtractorRegistry;
use Negarity\Color\Extractor\PerceivedWeightExtractor;
use Negarity\Color\Extractor\SaturationExtractor;
use Negarity\Color\Extractor\TemperatureExtractor;
use Negarity\Color\Extractor\VibrancyExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();

ExtractorRegistry::register(new TemperatureExtractor());
ExtractorRegistry::register(new BrightnessExtractor());
ExtractorRegistry::register(new SaturationExtractor());
ExtractorRegistry::register(new ChromaExtractor());
ExtractorRegistry::register(new PerceivedWeightExtractor());
ExtractorRegistry::register(new VibrancyExtractor());
ExtractorRegistry::register(new ContrastExtractor());

$colors = [
    'red'     => Color::rgb(255, 0, 0),
    'blue'    => Color::rgb(0, 0, 255),
    'yellow'  => Color::rgb(255, 255, 0),
    'white'   => Color::rgb(255, 255, 255),
    'black'   => Color::rgb(0, 0, 0),
    'gray50'  => Color::rgb(128, 128, 128),
    'lime'    => Color::rgb(0, 255, 0),
    'magenta' => Color::rgb(255, 0, 255),
];

/** Map extractor name => [extract param or null, labeler callable: value -> label] */
$extractors = [
    'temperature'       => [null, TemperatureExtractor::getLabelForValue(...)],
    'brightness'         => [null, BrightnessExtractor::getLabelForValue(...)],
    'saturation'         => [null, SaturationExtractor::getLabelForValue(...)],
    'chroma'             => [null, ChromaExtractor::getLabelForValue(...)],
    'perceived_weight'   => [null, PerceivedWeightExtractor::getLabelForValue(...)],
    'vibrancy'           => [null, VibrancyExtractor::getLabelForValue(...)],
    'contrast (white)'   => ['white', ContrastExtractor::getLabelForValue(...)],
    'contrast (black)'   => ['black', ContrastExtractor::getLabelForValue(...)],
];

echo "========== All extractors (value + label) ==========" . PHP_EOL;

foreach ($colors as $name => $color) {
    echo PHP_EOL . "--- $name: $color ---" . PHP_EOL;

    foreach ($extractors as $extractorName => [$param, $getLabel]) {
        $extractor = ExtractorRegistry::get($param === null ? $extractorName : 'contrast');
        $value = $extractor->extract($color, $param);
        $label = $getLabel($value);
        $valueStr = is_float($value) ? (string) round($value, 3) : (string) $value;
        echo "  " . str_pad($extractorName, 22) . " " . str_pad($valueStr, 10) . " → " . $label . PHP_EOL;
    }
}

echo PHP_EOL . "Done." . PHP_EOL;

<?php

/**
 * Extractors example: temperature, brightness, saturation, chroma, perceived weight, vibrancy, contrast.
 *
 * Run: php examples/Extractor/Extractors.php
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
    'red'    => Color::rgb(255, 0, 0),
    'blue'   => Color::rgb(0, 0, 255),
    'white'  => Color::rgb(255, 255, 255),
    'black'  => Color::rgb(0, 0, 0),
    'gray50' => Color::rgb(128, 128, 128),
    'lime'   => Color::rgb(0, 255, 0),
];

echo "========== Extractors (value + label) ==========" . PHP_EOL;

foreach ($colors as $name => $color) {
    echo PHP_EOL . "--- $name: $color ---" . PHP_EOL;

    $temp = ExtractorRegistry::get('temperature')->extract($color);
    echo "  temperature:      " . round($temp, 3) . "  → " . TemperatureExtractor::getLabelForValue($temp) . PHP_EOL;

    $bright = ExtractorRegistry::get('brightness')->extract($color);
    echo "  brightness:       " . round($bright, 2) . "  → " . BrightnessExtractor::getLabelForValue($bright) . PHP_EOL;

    $sat = ExtractorRegistry::get('saturation')->extract($color);
    echo "  saturation:       " . round($sat, 2) . "  → " . SaturationExtractor::getLabelForValue($sat) . PHP_EOL;

    $chroma = ExtractorRegistry::get('chroma')->extract($color);
    echo "  chroma:           " . round($chroma, 2) . "  → " . ChromaExtractor::getLabelForValue($chroma) . PHP_EOL;

    $weight = ExtractorRegistry::get('perceived_weight')->extract($color);
    echo "  perceived_weight: " . round($weight, 2) . "  → " . PerceivedWeightExtractor::getLabelForValue($weight) . PHP_EOL;

    $vibrancy = ExtractorRegistry::get('vibrancy')->extract($color);
    echo "  vibrancy:         " . round($vibrancy, 2) . "  → " . VibrancyExtractor::getLabelForValue($vibrancy) . PHP_EOL;

    $contrastWhite = ExtractorRegistry::get('contrast')->extract($color, 'white');
    $contrastBlack = ExtractorRegistry::get('contrast')->extract($color, 'black');
    echo "  contrast (white): " . round($contrastWhite, 2) . "  → " . ContrastExtractor::getLabelForValue($contrastWhite) . PHP_EOL;
    echo "  contrast (black): " . round($contrastBlack, 2) . "  → " . ContrastExtractor::getLabelForValue($contrastBlack) . PHP_EOL;
}

echo PHP_EOL . "Done." . PHP_EOL;

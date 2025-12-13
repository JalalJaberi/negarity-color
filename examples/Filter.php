<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;

// Register the BrightnessFilter
$brightnessFilter = new BrightnessFilter();
FilterRegistry::register($brightnessFilter);
// Retrieve and apply the filter
$color = Color::rgb(255, 0, 0); // Red color
$brighterColor = FilterRegistry::get('brightness')->apply($color, 20); // Increase brightness by 20%
$filteredColor = $color->brightness(20); // Using the Color method for brightness
echo "Original Color: " . $color->toHex() . PHP_EOL;
echo "Brighter Color: " . $brighterColor->toHex() . PHP_EOL;
echo "Filtered Color: " . $filteredColor->toHex() . PHP_EOL;
// Output:
// Original Color: #FF0000
// Brighter Color: #FF1414

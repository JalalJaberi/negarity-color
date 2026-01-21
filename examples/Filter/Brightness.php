<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Parameterized\BrightnessFilter;
use Negarity\Color\Registry\ColorSpaceRegistry;

// Register built-in color spaces
ColorSpaceRegistry::registerBuiltIn();

// Register the BrightnessFilter
$brightnessFilter = new BrightnessFilter();
FilterRegistry::register($brightnessFilter);
// Retrieve and apply the filter
$rgb = Color::rgb(255, 0, 0); // Red rgb
$brighterColor = FilterRegistry::get('brightness')->apply($rgb, 20); // Increase brightness by unit
$filteredColor = $rgb->brightness(20); // Using the Color method for brightness
echo " -------- RGB --------" . PHP_EOL;
echo "Original Color: " . $rgb . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$rgba = $rgb->toRgba(); // Convert to RGBA
$brighterColor = FilterRegistry::get('brightness')->apply($rgba, 20); // Increase brightness by unit
$filteredColor = $rgba->brightness(20); // Using the Color method for brightness
echo " -------- RGBA --------" . PHP_EOL;
echo "Original Color: " . $rgba . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$cmyk = $rgb->toCmyk(); // Convert to CMYK
$brighterColor = FilterRegistry::get('brightness')->apply($cmyk, 20); // Increase brightness by unit
$filteredColor = $cmyk->brightness(20); // Using the Color method for brightness
echo " -------- CMYK --------" . PHP_EOL;
echo "Original Color: " . $cmyk . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$hsl = $rgb->toHsl(); // Convert to HSL
$brighterColor = FilterRegistry::get('brightness')->apply($hsl, 20); // Increase brightness by unit
$filteredColor = $hsl->brightness(20); // Using the Color method for brightness
echo " -------- HSL --------" . PHP_EOL;
echo "Original Color: " . $hsl . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;
echo " -------- HSV --------" . PHP_EOL;

$hsla = $rgb->toHsla(); // Convert to HSLA
$brighterColor = FilterRegistry::get('brightness')->apply($hsla, 20); // Increase brightness by unit
$filteredColor = $hsla->brightness(20); // Using the Color method for brightness
echo "Original Color: " . $hsla . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$hsv = $rgb->toHsv(); // Convert to HSV
$brighterColor = FilterRegistry::get('brightness')->apply($hsv, 20); // Increase brightness by unit
$filteredColor = $hsv->brightness(20); // Using the Color method for brightness
echo "Original Color: " . $hsv . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$lab = $rgb->toLab(); // Convert to LAB
$brighterColor = FilterRegistry::get('brightness')->apply($lab, 20); // Increase brightness by unit
$filteredColor = $lab->brightness(20); // Using the Color method for brightness
echo " -------- LAB --------" . PHP_EOL;
echo "Original Color: " . $lab . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$lch = $rgb->toLch(); // Convert to LCH
$brighterColor = FilterRegistry::get('brightness')->apply($lch, 20); // Increase brightness by unit
$filteredColor = $lch->brightness(20); // Using the Color method for brightness
echo " -------- LCH --------" . PHP_EOL;
echo "Original Color: " . $lch . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$xyz = $rgb->toXyz(); // Convert to XYZ
$brighterColor = FilterRegistry::get('brightness')->apply($xyz, 20); // Increase brightness by unit
$filteredColor = $xyz->brightness(20); // Using the Color method for brightness
echo " -------- XYZ --------" . PHP_EOL;
echo "Original Color: " . $xyz . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$ycbcr = $rgb->toYcbcr(); // Convert to YCbCr
$brighterColor = FilterRegistry::get('brightness')->apply($ycbcr, 20); // Increase brightness by unit
$filteredColor = $ycbcr->brightness(20); // Using the Color method for brightness
echo " -------- YCbCr --------" . PHP_EOL;
echo "Original Color: " . $ycbcr . PHP_EOL;
echo "Brighter Color: " . $brighterColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;
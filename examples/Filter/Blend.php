<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Binary\BlendFilter;
use Negarity\Color\Registry\ColorSpaceRegistry;

// Register built-in color spaces
ColorSpaceRegistry::registerBuiltIn();

// Register the BlendFilter
$blendFilter = new BlendFilter();
FilterRegistry::register($blendFilter);
// Retrieve and apply the filter
$redRgb = Color::rgb(255, 0, 0); // Red rgb
$blueRgb = Color::rgb(0, 0, 255); // Blue rgb
$blendColor = FilterRegistry::get('blend')->apply($redRgb, $blueRgb); // Blend
$filteredColor = $redRgb->blend($blueRgb); // Using the Color method for blend
echo " -------- RGB --------" . PHP_EOL;
echo "Original Red Color: " . $redRgb . PHP_EOL;
echo "Original Blue Color: " . $blueRgb . PHP_EOL;
echo "Blended Color: " . $blendColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$redRgba = $redRgb->toRgba(); // Red rgba
$blueRgba = $blueRgb->toRgba(); // Blue rgba
$blendColorRgba = FilterRegistry::get('blend')->apply($redRgba, $blueRgba); // Blend
$filteredColorRgba = $redRgba->blend($blueRgba); // Using the Color method for blend
echo " ------- RGBA -------" . PHP_EOL;
echo "Original Red Color: " . $redRgba . PHP_EOL;
echo "Original Blue Color: " . $blueRgba . PHP_EOL;
echo "Blended Color: " . $blendColorRgba . PHP_EOL;
echo "Filtered Color: " . $filteredColorRgba . PHP_EOL;

$redCmyk = $redRgb->toCmyk(); // Red cmyk
$blueCmyk = $blueRgb->toCmyk(); // Blue cmyk
$blendColorCmyk = FilterRegistry::get('blend')->apply($redCmyk, $blueCmyk); // Blend
$filteredColorCmyk = $redCmyk->blend($blueCmyk); // Using the Color method for blend
echo " ------- CMYK -------" . PHP_EOL;
echo "Original Red Color: " . $redCmyk . PHP_EOL;
echo "Original Blue Color: " . $blueCmyk . PHP_EOL;
echo "Blended Color: " . $blendColorCmyk . PHP_EOL;
echo "Filtered Color: " . $filteredColorCmyk . PHP_EOL;

$redHsl = $redRgb->toHsl(); // Red hsl
$blueHsl = $blueRgb->toHsl(); // Blue hsl
$blendColorHsl = FilterRegistry::get('blend')->apply($redHsl, $blueHsl); // Blend
$filteredColorHsl = $redHsl->blend($blueHsl); // Using the Color method for blend
echo " ------- HSL -------" . PHP_EOL;
echo "Original Red Color: " . $redHsl . PHP_EOL;
echo "Original Blue Color: " . $blueHsl . PHP_EOL;
echo "Blended Color: " . $blendColorHsl . PHP_EOL;
echo "Filtered Color: " . $filteredColorHsl . PHP_EOL;

$redHsla = $redRgb->toHsla(); // Red hsla
$blueHsla = $blueRgba->toHsla(); // Blue hsla
$blendColorHsla = FilterRegistry::get('blend')->apply($redHsla, $blueHsla); // Blend
$filteredColorHsla = $redHsla->blend($blueHsla); // Using the Color method for blend
echo " ------- HSLA -------" . PHP_EOL;
echo "Original Red Color: " . $redHsla . PHP_EOL;
echo "Original Blue Color: " . $blueHsla . PHP_EOL;
echo "Blended Color: " . $blendColorHsla . PHP_EOL;
echo "Filtered Color: " . $filteredColorHsla . PHP_EOL;

$redHsv = $redRgb->toHsv(); // Red hsv
$blueHsv = $blueRgb->toHsv(); // Blue hsv
$blendColorHsv = FilterRegistry::get('blend')->apply($redHsv, $blueHsv); // Blend
$filteredColorHsv = $redHsv->blend($blueHsv); // Using the Color method for blend
echo " ------- HSV -------" . PHP_EOL;
echo "Original Red Color: " . $redHsv . PHP_EOL;
echo "Original Blue Color: " . $blueHsv . PHP_EOL;
echo "Blended Color: " . $blendColorHsv . PHP_EOL;
echo "Filtered Color: " . $filteredColorHsv . PHP_EOL;

$redLab = $redRgb->toLab(); // Red lab
$blueLab = $blueRgb->toLab(); // Blue lab
$blendColorLab = FilterRegistry::get('blend')->apply($redLab, $blueLab); // Blend
$filteredColorLab = $redLab->blend($blueLab); // Using the Color method for blend
echo " ------- LAB -------" . PHP_EOL;
echo "Original Red Color: " . $redLab . PHP_EOL;
echo "Original Blue Color: " . $blueLab . PHP_EOL;
echo "Blended Color: " . $blendColorLab . PHP_EOL;
echo "Filtered Color: " . $filteredColorLab . PHP_EOL;

$redLch = $redRgb->toLch(); // Red lch
$blueLch = $blueRgb->toLch(); // Blue lch
$blendColorLch = FilterRegistry::get('blend')->apply($redLch, $blueLch); // Blend
$filteredColorLch = $redLch->blend($blueLch); // Using the Color method for blend
echo " ------- LCH -------" . PHP_EOL;
echo "Original Red Color: " . $redLch . PHP_EOL;
echo "Original Blue Color: " . $blueLch . PHP_EOL;
echo "Blended Color: " . $blendColorLch . PHP_EOL;
echo "Filtered Color: " . $filteredColorLch . PHP_EOL;

$redXyz = $redRgb->toXyz(); // Red xyz
$blueXyz = $blueRgb->toXyz(); // Blue xyz
$blendColorXyz = FilterRegistry::get('blend')->apply($redXyz, $blueXyz); // Blend
$filteredColorXyz = $redXyz->blend($blueXyz); // Using the Color method for blend
echo " ------- XYZ -------" . PHP_EOL;
echo "Original Red Color: " . $redXyz . PHP_EOL;
echo "Original Blue Color: " . $blueXyz . PHP_EOL;
echo "Blended Color: " . $blendColorXyz . PHP_EOL;
echo "Filtered Color: " . $filteredColorXyz . PHP_EOL;

$redYcbcr = $redRgb->toYcbcr(); // Red ycbcr
$blueYcbcr = $blueRgb->toYcbcr(); // Blue ycbcr
$blendColorYcbcr = FilterRegistry::get('blend')->apply($redYcbcr, $blueYcbcr); // Blend
$filteredColorYcbcr = $redYcbcr->blend($blueYcbcr); // Using the Color method for blend
echo " ------- YCBCR -------" . PHP_EOL;
echo "Original Red Color: " . $redYcbcr . PHP_EOL;
echo "Original Blue Color: " . $blueYcbcr . PHP_EOL;
echo "Blended Color: " . $blendColorYcbcr . PHP_EOL;
echo "Filtered Color: " . $filteredColorYcbcr . PHP_EOL;

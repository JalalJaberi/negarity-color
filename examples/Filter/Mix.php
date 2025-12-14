<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Filter\FilterRegistry;
use Negarity\Color\Filter\Binary\MixFilter;

// Register the MixFilter
$mixFilter = new MixFilter(0.1);
FilterRegistry::register($mixFilter);
// Retrieve and apply the filter
$redRgb = Color::rgb(255, 0, 0); // Red rgb
$blueRgb = Color::rgb(0, 0, 255); // Blue rgb
$mixColor = FilterRegistry::get('mix')->apply($redRgb, $blueRgb); // Mix
$filteredColor = $redRgb->mix($blueRgb); // Using the Color method for mix
echo " -------- RGB --------" . PHP_EOL;
echo "Original Red Color: " . $redRgb . PHP_EOL;
echo "Original Blue Color: " . $blueRgb . PHP_EOL;
echo "Mixed Color: " . $mixColor . PHP_EOL;
echo "Filtered Color: " . $filteredColor . PHP_EOL;

$redRgba = $redRgb->toRgba(); // Red rgba
$blueRgba = $blueRgb->toRgba(); // Blue rgba
$mixColorRgba = FilterRegistry::get('mix')->apply($redRgba, $blueRgba); // Mix
$filteredColorRgba = $redRgba->mix($blueRgba); // Using the Color method for mix
echo " ------- RGBA -------" . PHP_EOL;
echo "Original Red Color: " . $redRgba . PHP_EOL;
echo "Original Blue Color: " . $blueRgba . PHP_EOL;
echo "Mixed Color: " . $mixColorRgba . PHP_EOL;
echo "Filtered Color: " . $filteredColorRgba . PHP_EOL;

$redCmyk = $redRgb->toCmyk(); // Red cmyk
$blueCmyk = $blueRgb->toCmyk(); // Blue cmyk
$mixColorCmyk = FilterRegistry::get('mix')->apply($redCmyk, $blueCmyk); // Mix
$filteredColorCmyk = $redCmyk->mix($blueCmyk); // Using the Color method for mix
echo " ------- CMYK -------" . PHP_EOL;
echo "Original Red Color: " . $redCmyk . PHP_EOL;
echo "Original Blue Color: " . $blueCmyk . PHP_EOL;
echo "Mixed Color: " . $mixColorCmyk . PHP_EOL;
echo "Filtered Color: " . $filteredColorCmyk . PHP_EOL;

$redHsl = $redRgb->toHsl(); // Red hsl
$blueHsl = $blueRgb->toHsl(); // Blue hsl
$mixColorHsl = FilterRegistry::get('mix')->apply($redHsl, $blueHsl); // Mix
$filteredColorHsl = $redHsl->mix($blueHsl); // Using the Color method for mix
echo " ------- HSL -------" . PHP_EOL;
echo "Original Red Color: " . $redHsl . PHP_EOL;
echo "Original Blue Color: " . $blueHsl . PHP_EOL;
echo "Mixed Color: " . $mixColorHsl . PHP_EOL;
echo "Filtered Color: " . $filteredColorHsl . PHP_EOL;

$redHsla = $redRgb->toHsla(); // Red hsla
$blueHsla = $blueRgba->toHsla(); // Blue hsla
$mixColorHsla = FilterRegistry::get('mix')->apply($redHsla, $blueHsla); // Mix
$filteredColorHsla = $redHsla->mix($blueHsla); // Using the Color method for mix
echo " ------- HSLA -------" . PHP_EOL;
echo "Original Red Color: " . $redHsla . PHP_EOL;
echo "Original Blue Color: " . $blueHsla . PHP_EOL;
echo "Mixed Color: " . $mixColorHsla . PHP_EOL;
echo "Filtered Color: " . $filteredColorHsla . PHP_EOL;

$redHsv = $redRgb->toHsv(); // Red hsv
$blueHsv = $blueRgb->toHsv(); // Blue hsv
$mixColorHsv = FilterRegistry::get('mix')->apply($redHsv, $blueHsv); // Mix
$filteredColorHsv = $redHsv->mix($blueHsv); // Using the Color method for mix
echo " ------- HSV -------" . PHP_EOL;
echo "Original Red Color: " . $redHsv . PHP_EOL;
echo "Original Blue Color: " . $blueHsv . PHP_EOL;
echo "Mixed Color: " . $mixColorHsv . PHP_EOL;
echo "Filtered Color: " . $filteredColorHsv . PHP_EOL;

$redLab = $redRgb->toLab(); // Red lab
$blueLab = $blueRgb->toLab(); // Blue lab
$mixColorLab = FilterRegistry::get('mix')->apply($redLab, $blueLab); // Mix
$filteredColorLab = $redLab->mix($blueLab); // Using the Color method for mix
echo " ------- LAB -------" . PHP_EOL;
echo "Original Red Color: " . $redLab . PHP_EOL;
echo "Original Blue Color: " . $blueLab . PHP_EOL;
echo "Mixed Color: " . $mixColorLab . PHP_EOL;
echo "Filtered Color: " . $filteredColorLab . PHP_EOL;

$redLch = $redRgb->toLch(); // Red lch
$blueLch = $blueRgb->toLch(); // Blue lch
$mixColorLch = FilterRegistry::get('mix')->apply($redLch, $blueLch); // Mix
$filteredColorLch = $redLch->mix($blueLch); // Using the Color method for mix
echo " ------- LCH -------" . PHP_EOL;
echo "Original Red Color: " . $redLch . PHP_EOL;
echo "Original Blue Color: " . $blueLch . PHP_EOL;
echo "Mixed Color: " . $mixColorLch . PHP_EOL;
echo "Filtered Color: " . $filteredColorLch . PHP_EOL;

$redXyz = $redRgb->toXyz(); // Red xyz
$blueXyz = $blueRgb->toXyz(); // Blue xyz
$mixColorXyz = FilterRegistry::get('mix')->apply($redXyz, $blueXyz); // Mix
$filteredColorXyz = $redXyz->mix($blueXyz); // Using the Color method for mix
echo " ------- XYZ -------" . PHP_EOL;
echo "Original Red Color: " . $redXyz . PHP_EOL;
echo "Original Blue Color: " . $blueXyz . PHP_EOL;
echo "Mixed Color: " . $mixColorXyz . PHP_EOL;
echo "Filtered Color: " . $filteredColorXyz . PHP_EOL;

$redYcbcr = $redRgb->toYcbcr(); // Red ycbcr
$blueYcbcr = $blueRgb->toYcbcr(); // Blue ycbcr
$mixColorYcbcr = FilterRegistry::get('mix')->apply($redYcbcr, $blueYcbcr); // Mix
$filteredColorYcbcr = $redYcbcr->mix($blueYcbcr); // Using the Color method for mix
echo " ------- YCBCR -------" . PHP_EOL;
echo "Original Red Color: " . $redYcbcr . PHP_EOL;
echo "Original Blue Color: " . $blueYcbcr . PHP_EOL;
echo "Mixed Color: " . $mixColorYcbcr . PHP_EOL;
echo "Filtered Color: " . $filteredColorYcbcr . PHP_EOL;

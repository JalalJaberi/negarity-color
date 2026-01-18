<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\Color;

// Create a color from RGB values
$primaryColor = Color::rgb(255, 100, 50);
echo "Primary Color: " . $primaryColor . "\n";
echo "Hex: " . $primaryColor->toHex() . "\n";

// Convert to different color spaces
$hsl = $primaryColor->toHSL();
echo "HSL: " . $hsl . "\n";

$cmyk = $primaryColor->toCMYK();
echo "CMYK: " . $cmyk . "\n";

// Create a color from hex
$secondaryColor = Color::hex('#3498db');
echo "Secondary Color: " . $secondaryColor . "\n";
echo "RGB values: R={$secondaryColor->getR()}, G={$secondaryColor->getG()}, B={$secondaryColor->getB()}\n";

// Modify colors
$lighterColor = $primaryColor->with(['r' => 255, 'g' => 150, 'b' => 100]);
echo "Lighter variant: " . $lighterColor . "\n";

// Create a color with alpha channel
$transparentColor = Color::rgba(255, 100, 50, 128);
echo "Transparent Color: " . $transparentColor . "\n";
echo "Hex with alpha: " . $transparentColor->toHex() . "\n";

// Convert HSL to RGB
$hslColor = Color::hsl(210, 50, 40);
$rgbFromHsl = $hslColor->toRGB();
echo "HSL Color: " . $hslColor . "\n";
echo "Converted to RGB: " . $rgbFromHsl . "\n";
echo "Hex: " . $rgbFromHsl->toHex() . "\n";
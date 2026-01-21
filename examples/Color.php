<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\ColorSpace\RGB;
use Negarity\Color\Registry\ColorSpaceRegistry;
use Negarity\Color\Registry\VGANamedColors;

// Register built-in color spaces
ColorSpaceRegistry::registerBuiltIn();

$color = new Color(RGB::class, ['r' => 255, 'g' => 100, 'b' => 50]);
echo $color->getColorSpaceName() .  PHP_EOL; // "rgb"
echo $color->getChannel('r') .  PHP_EOL; // 255
echo $color->getChannel('g') .  PHP_EOL; // 100
echo $color->getChannel('b') .  PHP_EOL; // 50
echo var_export($color->toArray(), false) .  PHP_EOL; // {"r":255,"g":100,"b":50}
echo json_encode($color) .  PHP_EOL; // {"r":255,"g":100,"b":50}
echo $color .  PHP_EOL; // "rgb(255, 100, 50)"
echo $color->toHex() .  PHP_EOL; // "#FF6432"
$withoutR = $color->without(['r']);
echo json_encode($withoutR->toArray()) .  PHP_EOL; // {"r":0,"g":100,"b":50}
$withoutG = $color->without(['g']);
echo json_encode($withoutG->toArray()) .  PHP_EOL; // {"r":255,"g":0,"b":50}
$withoutB = $color->without(['b']);
echo json_encode($withoutB->toArray()) .  PHP_EOL; // {"r":255,"g":100,"b":0}
$withR = $color->with(['r' => 200]);
echo json_encode($withR->toArray()) .  PHP_EOL; // {"r":200,"g":100,"b":50}
$withG = $color->with(['g' => 150]);
echo json_encode($withG->toArray()) .  PHP_EOL; // {"r":255,"g":150,"b":50}
$withB = $color->with(['b' => 75]);
echo json_encode($withB->toArray()) .  PHP_EOL; // {"r":255,"g":100,"b":75}

echo 'Using named colors:' . PHP_EOL;

Color::addRegistry(new VGANamedColors());

$red = Color::red();
echo 'red:' . json_encode($red) .  PHP_EOL; // "rgb(255, 0, 0)"
$navy = Color::navy();
echo 'navy:' . json_encode($navy) .  PHP_EOL; // "rgb(0, 0, 128)"

<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\RGBA;
use Negarity\Color\Color;

$color = new Color(RGBA::class, ['r' => 255, 'g' => 100, 'b' => 50, 'a' => 255]);
echo 'name => ' . $color->getColorSpaceName() .  PHP_EOL; // "rgba"
echo 'r => ' . $color->getChannel('r') .  PHP_EOL; // 255
echo 'r => ' . $color->getR() .  PHP_EOL; // 255
echo 'g => ' . $color->getChannel('g') .  PHP_EOL; // 100
echo 'g => ' . $color->getG() .  PHP_EOL; // 100
echo 'b => ' . $color->getChannel('b') .  PHP_EOL; // 50
echo 'b => ' . $color->getB() .  PHP_EOL; // 50
echo 'a => ' . $color->getChannel('a') .  PHP_EOL; // 255
echo 'a => ' . $color->getA() .  PHP_EOL; // 255
echo var_export($color->toArray(), false) .  PHP_EOL; // {"r":255,"g":100,"b":50,"a":255}
echo json_encode($color) .  PHP_EOL; // {"r":255,"g":100,"b":50,"a":255}
echo $color .  PHP_EOL; // "rgba(255, 100, 50, 1.00)"
$withoutR = $color->without(['r']);
echo json_encode($withoutR->toArray()) .  PHP_EOL; // {"r":0,"g":100,"b":50,"a":255}
$withoutG = $color->without(['g']);
echo json_encode($withoutG->toArray()) .  PHP_EOL; // {"r":255,"g":0,"b":50,"a":255}
$withoutB = $color->without(['b']);
echo json_encode($withoutB->toArray()) .  PHP_EOL; // {"r":255,"g":100,"b":0,"a":255}
$withoutA = $color->without(['a']);
echo json_encode($withoutA->toArray()) .  PHP_EOL; // {"r":255,"g":100,"b":50,"a":0}
$withR = $color->with(['r' => 200]);
echo json_encode($withR->toArray()) .  PHP_EOL; // {"r":200,"g":100,"b":50,"a":255}
$withG = $color->with(['g' => 150]);
echo json_encode($withG->toArray()) .  PHP_EOL; // {"r":255,"g":150,"b":50,"a":255}
$withB = $color->with(['b' => 75]);
echo json_encode($withB->toArray()) .  PHP_EOL; // {"r":255,"g":100,"b":75,"a":255}
$withA = $color->with(['a' => 128]);
echo json_encode($withA->toArray()) .  PHP_EOL; // {"r":255,"g":100,"b":50,"a":128}
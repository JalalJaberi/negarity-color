<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\RGBA;

$color = new RGBA(255, 100, 50);
echo $color->getName() .  PHP_EOL; // "rgba"
echo $color->getChannel('r') .  PHP_EOL; // 255
echo $color->getR() .  PHP_EOL; // 255
echo $color->getChannel('g') .  PHP_EOL; // 100
echo $color->getG() .  PHP_EOL; // 100
echo $color->getChannel('b') .  PHP_EOL; // 50
echo $color->getB() .  PHP_EOL; // 50
echo $color->getChannel('a') .  PHP_EOL; // 255
echo $color->getA() .  PHP_EOL; // 255
echo json_encode($color->toArray()) .  PHP_EOL; // {"r":255,"g":100,"b":50,"a":255}
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
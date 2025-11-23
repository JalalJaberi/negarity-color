<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\RGB;

$color = new RGB(255, 100, 50);
echo $color->getName() .  PHP_EOL; // "rgb"
echo $color->getChannel('r') .  PHP_EOL; // 255
echo $color->getR() .  PHP_EOL; // 255
echo $color->getChannel('g') .  PHP_EOL; // 100
echo $color->getG() .  PHP_EOL; // 100
echo $color->getChannel('b') .  PHP_EOL; // 50
echo $color->getB() .  PHP_EOL; // 50
echo json_encode($color->toArray()) .  PHP_EOL; // {"r":255,"g":100,"b":50}
echo $color .  PHP_EOL; // "rgb(255, 100, 50)"
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
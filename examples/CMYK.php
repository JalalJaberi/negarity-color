<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\CMYK;
use Negarity\Color\Color;
use Negarity\Color\Registry\ColorSpaceRegistry;

// Register built-in color spaces
ColorSpaceRegistry::registerBuiltIn();

$color = new Color(CMYK::class, ['c' => 0, 'm' => 50, 'y' => 100, 'k' => 0]);
echo 'name => ' . $color->getColorSpaceName() .  PHP_EOL; // "cmyk"
echo 'c => ' . $color->getChannel('c') .  PHP_EOL; // 0
echo 'c => ' . $color->getC() .  PHP_EOL; // 0
echo 'm => ' . $color->getChannel('m') .  PHP_EOL; // 50
echo 'm => ' . $color->getM() .  PHP_EOL; // 50
echo 'y => ' . $color->getChannel('y') .  PHP_EOL; // 100
echo 'y => ' . $color->getY() .  PHP_EOL; // 100
echo 'k => ' . $color->getChannel('k') .  PHP_EOL; // 0
echo 'k => ' . $color->getK() .  PHP_EOL; // 0
echo var_export($color->toArray(), false) .  PHP_EOL; // {"c":0,"m":50,"y":100,"k":0}
echo json_encode($color) .  PHP_EOL; // {"c":0,"m":50,"y":100,"k":0}
echo $color .  PHP_EOL; // "cmyk(0%, 50%, 100%, 0%)"
$withoutC = $color->without(['c']);
echo json_encode($withoutC->toArray()) .  PHP_EOL; // {"m":50,"y":100,"k":0}
$withoutM = $color->without(['m']);
echo json_encode($withoutM->toArray()) .  PHP_EOL; // {"c":0,"y":100,"k":0}
$withoutY = $color->without(['y']);
echo json_encode($withoutY->toArray()) .  PHP_EOL; // {"c":0,"m":50,"k":0}
$withoutK = $color->without(['k']);
echo json_encode($withoutK->toArray()) .  PHP_EOL; // {"c":0,"m":50,"y":100}
$withC = $color->with(['c' => 25]);
echo json_encode($withC->toArray()) .  PHP_EOL; // {"c":25,"m":50,"y":100,"k":0}
$withM = $color->with(['m' => 75]);
echo json_encode($withM->toArray()) .  PHP_EOL; // {"c":0,"m":75,"y":100,"k":0}
$withY = $color->with(['y' => 50]);
echo json_encode($withY->toArray()) .  PHP_EOL; // {"c":0,"m":50,"y":50,"k":0}
$withK = $color->with(['k' => 10]);
echo json_encode($withK->toArray()) .  PHP_EOL; // {"c":0,"m":50,"y":100,"k":10}

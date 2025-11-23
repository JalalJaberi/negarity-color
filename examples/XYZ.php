<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\XYZ;

$color = new XYZ(41, 21, 1);
echo $color->getName() .  PHP_EOL; // "xyz"
echo $color->getChannel('x') .  PHP_EOL; // 41
echo $color->getX() .  PHP_EOL; // 41
echo $color->getChannel('y') .  PHP_EOL; // 21
echo $color->getY() .  PHP_EOL; // 21
echo $color->getChannel('z') .  PHP_EOL; // 1
echo $color->getZ() .  PHP_EOL; // 1
echo json_encode($color->toArray()) .  PHP_EOL; // {"x":41,"y":21,"z":1}
echo $color .  PHP_EOL; // "xyz(41, 21, 1)"
$withoutX = $color->without(['x']);
echo json_encode($withoutX->toArray()) .  PHP_EOL; // {"x":0,"y":21,"z":1}
$withoutY = $color->without(['y']);
echo json_encode($withoutY->toArray()) .  PHP_EOL; // {"x":41,"y":0,"z":1}
$withoutZ = $color->without(['z']);
echo json_encode($withoutZ->toArray()) .  PHP_EOL; // {"x":41,"y":21,"z":0}
$withX = $color->with(['x' => 50]);
echo json_encode($withX->toArray()) .  PHP_EOL; // {"x":50,"y":21,"z":1}
$withY = $color->with(['y' => 30]);
echo json_encode($withY->toArray()) .  PHP_EOL; // {"x":41,"y":30,"z":1}
$withZ = $color->with(['z' => 10]);
echo json_encode($withZ->toArray()) .  PHP_EOL; // {"x":41,"y":21,"z":10}

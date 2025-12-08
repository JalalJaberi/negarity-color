<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\HSLA;

$color = new HSLA(240, 100, 50, 255);
echo $color->getName() .  PHP_EOL; // "hsla"
echo $color->getChannel('h') .  PHP_EOL; // 240
echo $color->getH() .  PHP_EOL; // 240
echo $color->getChannel('s') .  PHP_EOL; // 100
echo $color->getS() .  PHP_EOL; // 100
echo $color->getChannel('l') .  PHP_EOL; // 50
echo $color->getL() .  PHP_EOL; // 50
echo $color->getChannel('a') .  PHP_EOL; // 255
echo $color->getA() .  PHP_EOL; // 255
echo var_export($color->toArray(), false) .  PHP_EOL; // {"h":240,"s":100,"l":50,"a":255}
echo json_encode($color) .  PHP_EOL; // {"h":240,"s":100,"l":50,"a":255}
echo $color .  PHP_EOL; // "hsla(240, 100%, 50%, 1)"
$withoutH = $color->without(['h']);
echo json_encode($withoutH->toArray()) .  PHP_EOL; // {"s":100,"l":50,"a":255}
$withoutS = $color->without(['s']);
echo json_encode($withoutS->toArray()) .  PHP_EOL; // {"h":240,"l":50,"a":255}
$withoutL = $color->without(['l']);
echo json_encode($withoutL->toArray()) .  PHP_EOL; // {"h":240,"s":100,"a":255}
$withoutA = $color->without(['a']);
echo json_encode($withoutA->toArray()) .  PHP_EOL; // {"h":240,"s":100,"l":50}
$withH = $color->with(['h' => 120]);
echo json_encode($withH->toArray()) .  PHP_EOL; // {"h":120,"s":100,"l":50,"a":255}
$withS = $color->with(['s' => 50]);
echo json_encode($withS->toArray()) .  PHP_EOL; // {"h":240,"s":50,"l":50,"a":255}
$withL = $color->with(['l' => 75]);
echo json_encode($withL->toArray()) .  PHP_EOL; // {"h":240,"s":100,"l":75,"a":255}
$withA = $color->with(['a' => 128]);
echo json_encode($withA->toArray()) .  PHP_EOL; // {"h":240,"s":100,"l":50,"a":128}

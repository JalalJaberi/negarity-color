<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\LCh;

$color = new LCh(70, 50, 180);
echo $color->getName() .  PHP_EOL; // "lch"
echo $color->getChannel('l') .  PHP_EOL; // 70
echo $color ->getL() .  PHP_EOL; // 70
echo $color->getChannel('c') .  PHP_EOL; // 50
echo $color->getC() .  PHP_EOL; // 50
echo $color->getChannel('h') .  PHP_EOL; // 180
echo $color->getH() .  PHP_EOL; // 180
echo json_encode($color->toArray()) .  PHP_EOL; // {"l":70,"c":50,"h":180}
echo $color .  PHP_EOL; // "lch(70, 50, 180)"
$withoutL = $color->without(['l']);
echo json_encode($withoutL->toArray()) .  PHP_EOL; // {"l":0,"c":50,"h":180}
$withoutC = $color->without(['c']);
echo json_encode($withoutC->toArray()) .  PHP_EOL; // {"l":70,"c":0,"h":180}
$withoutH = $color->without(['h']);
echo json_encode($withoutH->toArray()) .  PHP_EOL; // {"l":70,"c":50,"h":0}
$withL = $color->with(['l' => 80]);
echo json_encode($withL->toArray()) .  PHP_EOL; // {"l":80,"c":50,"h":180}
$withC = $color->with(['c' => 60]);
echo json_encode($withC->toArray()) .  PHP_EOL; // {"l":70,"c":60,"h":180}
$withH = $color->with(['h' => 200]);
echo json_encode($withH->toArray()) .  PHP_EOL; // {"l":70,"c":50,"h":200}

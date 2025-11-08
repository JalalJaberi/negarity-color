<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\YCbCr;

$color = new YCbCr(128, 100, 150);
echo $color->getName() .  PHP_EOL; // "ycbcr"
echo $color->getChannel('y') .  PHP_EOL; // 128
echo $color->getChannel('cb') .  PHP_EOL; // 100
echo $color->getChannel('cr') .  PHP_EOL; // 150
echo json_encode($color->toArray()) .  PHP_EOL; // {"y":128,"cb":100,"cr":150}
echo $color .  PHP_EOL; // "ycbcr(128, 100, 150)"
$withoutY = $color->without(['y']);
echo json_encode($withoutY->toArray()) .  PHP_EOL; // {"y":0,"cb":100,"cr":150}
$withoutCb = $color->without(['cb']);
echo json_encode($withoutCb->toArray()) .  PHP_EOL; // {"y":128,"cb":0,"cr":150}
$withoutCr = $color->without(['cr']);
echo json_encode($withoutCr->toArray()) .  PHP_EOL; // {"y":128,"cb":100,"cr":0}
$withY = $color->with(['y' => 200]);
echo json_encode($withY->toArray()) .  PHP_EOL; // {"y":200,"cb":100,"cr":150}
$withCb = $color->with(['cb' => 120]);
echo json_encode($withCb->toArray()) .  PHP_EOL; // {"y":128,"cb":120,"cr":150}
$withCr = $color->with(['cr' => 180]);
echo json_encode($withCr->toArray()) .  PHP_EOL; // {"y":128,"cb":100,"cr":180}

<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\YCbCr;
use Negarity\Color\Color;

$color = new Color(YCbCr::class, ['y' => 78.5, 'cb' => 100, 'cr' => -100]);
echo 'name => ' . $color->getColorSpaceName() .  PHP_EOL; // "ycbcr"
echo 'y => ' . $color->getChannel('y') .  PHP_EOL; // 78.5
echo 'y => ' . $color->getY() .  PHP_EOL; // 78.5
echo 'cb => ' . $color->getChannel('cb') .  PHP_EOL; // 100
echo 'cb => ' . $color->getCb() .  PHP_EOL; // 100
echo 'cr => ' . $color->getChannel('cr') .  PHP_EOL; // -100
echo 'cr => ' . $color->getCr() .  PHP_EOL; // -100
echo var_export($color->toArray(), false) .  PHP_EOL; // {"y":78.5,"cb":100,"cr":-100}
echo json_encode($color) .  PHP_EOL; // {"y":78.5,"cb":100,"cr":-100}
echo $color .  PHP_EOL; // "ycbcr(78.5, 100, -100)"
$withoutY = $color->without(['y']);
echo json_encode($withoutY->toArray()) .  PHP_EOL; // {"y":0,"cb":100,"cr":-100}
$withoutCb = $color->without(['cb']);
echo json_encode($withoutCb->toArray()) .  PHP_EOL; // {"y":78.5,"cb":0,"cr":-100}
$withoutCr = $color->without(['cr']);
echo json_encode($withoutCr->toArray()) .  PHP_EOL; // {"y":78.5,"cb":100,"cr":0}
$withY = $color->with(['y' => 50]);
echo json_encode($withY->toArray()) .  PHP_EOL; // {"y":50,"cb":100,"cr":-100}
$withCb = $color->with(['cb' => 120]);
echo json_encode($withCb->toArray()) .  PHP_EOL; // {"y":78.5,"cb":120,"cr":-100}
$withCr = $color->with(['cr' => 100]);
echo json_encode($withCr->toArray()) .  PHP_EOL; // {"y":78.5,"cb":100,"cr":100}

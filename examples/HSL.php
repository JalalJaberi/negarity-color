<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\Color;

$color = new Color(HSL::class, ['h' => 210, 's' => 50, 'l' => 40]);
echo 'name => ' . $color->getColorSpaceName() .  PHP_EOL; // "hsl"
echo 'h => ' . $color->getChannel('h') .  PHP_EOL; // 210
echo 'h => ' . $color->getH() .  PHP_EOL; // 210
echo 's => ' . $color->getChannel('s') .  PHP_EOL; // 50
echo 's => ' . $color->getS() .  PHP_EOL; // 50
echo 'l => ' . $color->getChannel('l') .  PHP_EOL; // 40
echo 'l => ' . $color->getL() .  PHP_EOL; // 40
echo var_export($color->toArray(), false) .  PHP_EOL; // {"h":210,"s":50,"l":40}
echo json_encode($color) .  PHP_EOL; // {"h":210,"s":50,"l":40}
echo $color .  PHP_EOL; // "hsl(210, 50%, 40%)"
$withoutH = $color->without(['h']);
echo json_encode($withoutH->toArray()) .  PHP_EOL; // {"h":0,"s":50,"l":40}
$withoutS = $color->without(['s']);
echo json_encode($withoutS->toArray()) .  PHP_EOL; // {"h":210,"s":0,"l":40}
$withoutL = $color->without(['l']);
echo json_encode($withoutL->toArray()) .  PHP_EOL; // {"h":210,"s":50,"l":0}
$withH = $color->with(['h' => 180]);
echo json_encode($withH->toArray()) .  PHP_EOL; // {"h":180,"s":50,"l":40}
$withS = $color->with(['s' => 75]);
echo json_encode($withS->toArray()) .  PHP_EOL; // {"h":210,"s":75,"l":40}
$withL = $color->with(['l' => 60]);
echo json_encode($withL->toArray()) .  PHP_EOL; // {"h":210,"s":50,"l":60}

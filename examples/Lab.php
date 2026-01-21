<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Negarity\Color\ColorSpace\Lab;
use Negarity\Color\Color;
use Negarity\Color\Registry\ColorSpaceRegistry;

// Register built-in color spaces
ColorSpaceRegistry::registerBuiltIn();

$color = new Color(Lab::class, ['l' => 50, 'a' => 20, 'b' => -30]);
echo 'name => ' . $color->getColorSpaceName() .  PHP_EOL; // "lab"
echo 'l => ' . $color->getChannel('l') .  PHP_EOL; // 50
echo 'l => ' . $color->getL() .  PHP_EOL; // 50
echo 'a => ' . $color->getChannel('a') .  PHP_EOL; // 20
echo 'a => ' . $color->getA() .  PHP_EOL; // 20
echo 'b => ' . $color->getChannel('b') .  PHP_EOL; // -30
echo 'b => ' . $color->getB() .  PHP_EOL; // -30
echo var_export($color->toArray(), false) .  PHP_EOL; // {"l":50,"a":20,"b":-30}
echo json_encode($color) .  PHP_EOL; // {"l":50,"a":20,"b":-30}
echo $color .  PHP_EOL; // "lab(50, 20, -30)"
$withoutL = $color->without(['l']);
echo json_encode($withoutL->toArray()) .  PHP_EOL; // {"l":0,"a":20,"b":-30}
$withoutA = $color->without(['a']);
echo json_encode($withoutA->toArray()) .  PHP_EOL; // {"l":50,"a":0,"b":-30}
$withoutB = $color->without(['b']);
echo json_encode($withoutB->toArray()) .  PHP_EOL; // {"l":50,"a":20,"b":0}
$withL = $color->with(['l' => 70]);
echo json_encode($withL->toArray()) .  PHP_EOL; // {"l":70,"a":20,"b":-30}
$withA = $color->with(['a' => -10]);
echo json_encode($withA->toArray()) .  PHP_EOL; // {"l":50,"a":-10,"b":-30}
$withB = $color->with(['b' => 15]);
echo json_encode($withB->toArray()) .  PHP_EOL; // {"l":50,"a":20,"b":15}


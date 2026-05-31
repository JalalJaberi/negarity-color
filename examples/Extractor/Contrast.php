<?php

/**
 * Contrast extractor: WCAG (default), Michelson, Weber, RMS, ΔE76 — vs white and black.
 *
 * Run: php examples/Extractor/Contrast.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Negarity\Color\Color;
use Negarity\Color\Extractor\ContrastExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;

ColorSpaceRegistry::registerBuiltIn();

$extractor = new ContrastExtractor();

$algorithms = [
    ContrastExtractor::ALGORITHM_WCAG_CONTRAST_RATIO,
    ContrastExtractor::ALGORITHM_MICHELSON_CONTRAST,
    ContrastExtractor::ALGORITHM_WEBER_CONTRAST,
    ContrastExtractor::ALGORITHM_RMS_CONTRAST,
    ContrastExtractor::ALGORITHM_DELTA_E76,
];

$references = ['white', 'black'];

$samples = [
    'red'   => Color::rgb(255, 0, 0),
    'gray'  => Color::rgb(128, 128, 128),
    'navy'  => Color::rgb(0, 0, 128),
];

echo '========== ContrastExtractor ==========' . PHP_EOL;
echo PHP_EOL;
echo 'Default: WCAG contrast ratio. References: white and black.' . PHP_EOL;
echo PHP_EOL;

foreach ($samples as $name => $color) {
    echo "--- {$name} ---" . PHP_EOL;

    foreach ($algorithms as $algo) {
        $label = ContrastExtractor::getAlgorithmLabel($algo);
        echo "  {$label}" . PHP_EOL;

        foreach ($references as $ref) {
            $value = $extractor->extract($color, [
                'algorithm' => $algo,
                'contrastWith' => $ref,
            ]);
            $valueLabel = ContrastExtractor::getLabelForValue($value, $algo);

            printf(
                "    vs %-5s  %10.3f  → %s\n",
                $ref,
                $value,
                $valueLabel
            );
        }
    }

    echo PHP_EOL;
}

echo 'Legacy API (WCAG only):' . PHP_EOL;
$red = $samples['red'];
echo '  extract(red, "white") = ' . number_format($extractor->extract($red, 'white'), 2) . PHP_EOL;
echo '  extract(red, "black") = ' . number_format($extractor->extract($red, 'black'), 2) . PHP_EOL;

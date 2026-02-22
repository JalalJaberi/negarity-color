<?php

declare(strict_types=1);

namespace Negarity\Color\Extractor;

use Negarity\Color\ColorInterface;

/**
 * Base interface for color extractors.
 * Extractors produce a value (or values) from a color and optional parameters.
 * Implementors should also provide a static getLabelForValue(mixed $value): string
 * to return a human-readable label for a given extracted value (or value range).
 */
interface ExtractorInterface
{
    /**
     * Get the name used to identify this extractor (e.g. for registry lookup).
     */
    public function getName(): string;

    /**
     * Extract a value from the color.
     *
     * @param ColorInterface $color The color to analyze.
     * @param mixed $params Optional parameters (e.g. for Contrast: other color or 'white'/'black').
     * @return float|string The extracted value (numeric or category slug). Use getLabelForValue() for a human label.
     */
    public function extract(ColorInterface $color, mixed $params = null): float|string;
}

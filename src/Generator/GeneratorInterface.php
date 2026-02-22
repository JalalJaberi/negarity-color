<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

use Negarity\Color\ColorInterface;

/**
 * Base interface for color generators (e.g. complementary, triadic).
 * Generators produce a new color from an input color and an optional parameter.
 */
interface GeneratorInterface
{
    /**
     * Get the name used to invoke this generator (e.g. $color->complementary()).
     */
    public function getName(): string;

    /**
     * Generate a color from the given color and optional value (e.g. method enum/string).
     */
    public function apply(ColorInterface $color, mixed $value = null): ColorInterface;
}

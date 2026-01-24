<?php

declare(strict_types=1);

namespace Negarity\Color\Exception;

/**
 * Exception thrown when a named color is not found in a registry.
 */
class NamedColorNotFoundException extends \RuntimeException implements ColorExceptionInterface
{
}

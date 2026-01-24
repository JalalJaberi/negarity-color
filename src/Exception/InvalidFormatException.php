<?php

declare(strict_types=1);

namespace Negarity\Color\Exception;

/**
 * Exception thrown when a color format is invalid or malformed.
 */
class InvalidFormatException extends \InvalidArgumentException implements ColorExceptionInterface
{
}

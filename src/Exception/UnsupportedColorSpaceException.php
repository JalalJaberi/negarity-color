<?php

declare(strict_types=1);

namespace Negarity\Color\Exception;

/**
 * Exception thrown when a color space is not supported for a specific operation.
 */
class UnsupportedColorSpaceException extends \RuntimeException implements ColorExceptionInterface
{
}

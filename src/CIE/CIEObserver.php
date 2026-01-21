<?php

declare(strict_types=1);

namespace Negarity\Color\CIE;

/**
 * CIE Standard Observers.
 * 
 * Standard observers define the color matching functions representing
 * the human eye's response to different wavelengths of light.
 */
enum CIEObserver: string
{
    /**
     * CIE 1931 2° Standard Observer.
     * Used for small field of view (typically < 4°).
     */
    case TwoDegree = '2°';

    /**
     * CIE 1964 10° Standard Observer.
     * Used for larger field of view (typically > 4°).
     */
    case TenDegree = '10°';
}

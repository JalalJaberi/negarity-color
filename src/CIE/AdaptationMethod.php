<?php

declare(strict_types=1);

namespace Negarity\Color\CIE;

/**
 * Chromatic Adaptation Methods.
 * 
 * Methods for converting colors between different illuminants,
 * accounting for how the human visual system adapts to different light sources.
 */
enum AdaptationMethod: string
{
    /**
     * Bradford chromatic adaptation transform.
     * Most commonly used method, provides good results for most applications.
     */
    case Bradford = 'Bradford';

    /**
     * Von Kries chromatic adaptation transform.
     * One of the earliest and simplest adaptation methods.
     */
    case VonKries = 'VonKries';

    /**
     * XYZ Scaling chromatic adaptation.
     * Simple scaling method, less accurate but computationally efficient.
     */
    case XYZScaling = 'XYZScaling';
}

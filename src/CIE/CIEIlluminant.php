<?php

declare(strict_types=1);

namespace Negarity\Color\CIE;

/**
 * CIE Standard Illuminants.
 * 
 * Standard illuminants define the spectral power distribution of light sources
 * used in color science and colorimetry.
 */
enum CIEIlluminant: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D50 = 'D50';
    case D55 = 'D55';
    case D65 = 'D65';
    case D75 = 'D75';
    case E = 'E';
    case F1 = 'F1';
    case F2 = 'F2';
    case F3 = 'F3';
    case F4 = 'F4';
    case F5 = 'F5';
    case F6 = 'F6';
    case F7 = 'F7';
    case F8 = 'F8';
    case F9 = 'F9';
    case F10 = 'F10';
    case F11 = 'F11';
    case F12 = 'F12';
}

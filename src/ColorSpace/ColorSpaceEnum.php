<?php

namespace Negarity\Color\ColorSpace;

enum ColorSpaceEnum: string
{
    case RGB = 'rgb';
    case RGBA = 'rgba';
    case CMYK = 'cmyk';
    case HSL = 'hsl';
    case HSLA = 'hsla';
    case HSV = 'hsv';
    case LAB = 'lab';
    case LCH = 'lch';
    case XYZ = 'xyz';
    case YCBCR = 'ycbcr';
}
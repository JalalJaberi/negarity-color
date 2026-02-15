<?php

declare(strict_types=1);

namespace Negarity\Color\Filter\Unary;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\ComplementMethod;
use Negarity\Color\Filter\Parameterized\ParameterizedColorFilterInterface;
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\ColorSpace\RGB;
use Negarity\Color\ColorSpace\LCh;

/**
 * Complementary color filter.
 *
 * Computes the complementary color using the chosen method:
 * - Artistic: HSL hue + 180° (common in design tools).
 * - Perceptual: LCh hue + 180° (perceptually uniform).
 * - DisplayAccurate: RGB invert (255 - r, g, b).
 */
final class ComplementaryFilter implements ParameterizedColorFilterInterface
{
    #[\Override]
    public function getName(): string
    {
        return 'complementary';
    }

    #[\Override]
    public function apply(ColorInterface $color, mixed $value = null): ColorInterface
    {
        $method = ComplementMethod::fromValue($value);
        $illuminant = $color->getIlluminant();
        $observer = $color->getObserver();
        $originalSpaceName = $color->getColorSpaceName();
        $originalSpaceClass = $color->getColorSpace();

        $complement = match ($method) {
            ComplementMethod::DisplayAccurate => $this->complementDisplayAccurate($color, $illuminant, $observer),
            ComplementMethod::Artistic => $this->complementArtistic($color, $illuminant, $observer),
            ComplementMethod::Perceptual => $this->complementPerceptual($color, $illuminant, $observer),
        };

        $result = $this->convertToOriginalSpace($complement, $originalSpaceName);

        if ($originalSpaceClass::supportAlphaChannel()) {
            $alphaChannel = $originalSpaceClass::getAlphaChannelName();
            if ($alphaChannel !== '' && in_array($alphaChannel, $color->getChannels(), true)) {
                $result = $result->with([$alphaChannel => $color->getChannel($alphaChannel)]);
            }
        }

        return $result;
    }

    private function complementDisplayAccurate(ColorInterface $color, $illuminant, $observer): Color
    {
        $rgb = $color->toRGB();
        $r = 255.0 - $rgb->getChannel('r');
        $g = 255.0 - $rgb->getChannel('g');
        $b = 255.0 - $rgb->getChannel('b');
        return new Color(RGB::class, ['r' => $r, 'g' => $g, 'b' => $b], $illuminant, $observer);
    }

    private function complementArtistic(ColorInterface $color, $illuminant, $observer): Color
    {
        $hsl = $color->toHSL();
        $h = $hsl->getChannel('h');
        $s = $hsl->getChannel('s');
        $l = $hsl->getChannel('l');
        $h2 = fmod($h + 180.0, 360.0);
        if ($h2 < 0) {
            $h2 += 360.0;
        }
        return new Color(HSL::class, ['h' => $h2, 's' => $s, 'l' => $l], $illuminant, $observer);
    }

    private function complementPerceptual(ColorInterface $color, $illuminant, $observer): Color
    {
        $lch = $color->toLCh();
        $l = $lch->getChannel('l');
        $c = $lch->getChannel('c');
        $h = $lch->getChannel('h');
        $h2 = fmod($h + 180.0, 360.0);
        if ($h2 < 0) {
            $h2 += 360.0;
        }
        return new Color(LCh::class, ['l' => $l, 'c' => $c, 'h' => $h2], $illuminant, $observer);
    }

    private function convertToOriginalSpace(Color $complement, string $originalSpaceName): ColorInterface
    {
        $method = 'to' . ucfirst(strtolower($originalSpaceName));
        if (!is_callable([$complement, $method])) {
            return $complement->toRGB();
        }
        return $complement->$method();
    }
}

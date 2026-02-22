<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\Filter\Parameterized\ParameterizedColorFilterInterface;
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\ColorSpace\LCh;
use Negarity\Color\ColorSpace\HSV;

/**
 * Triadic color generator.
 *
 * Computes one of the two triadic colors (base hue + 120° or + 240°) using the chosen method:
 * - Artistic: HSL hue + 120° or + 240° (common in design tools).
 * - Perceptual: LCh hue + 120° or + 240° (perceptually uniform).
 * - DisplayAccurate: HSV hue + 120° or + 240°, then convert back to RGB.
 *
 * Register two instances with FilterRegistry for both triadic colors:
 *   FilterRegistry::register(new TriadicGenerator(120));  // triadic1
 *   FilterRegistry::register(new TriadicGenerator(240));  // triadic2
 */
final class TriadicGenerator implements ParameterizedColorFilterInterface
{
    private const NAME_TRIADIC_1 = 'triadic1';
    private const NAME_TRIADIC_2 = 'triadic2';

    public function __construct(
        private readonly int $hueOffset
    ) {
        if ($hueOffset !== 120 && $hueOffset !== 240) {
            throw new \InvalidArgumentException('TriadicGenerator hue offset must be 120 or 240.');
        }
    }

    #[\Override]
    public function getName(): string
    {
        return $this->hueOffset === 120 ? self::NAME_TRIADIC_1 : self::NAME_TRIADIC_2;
    }

    #[\Override]
    public function apply(ColorInterface $color, mixed $value = null): ColorInterface
    {
        $method = TriadicMethod::fromValue($value);
        $illuminant = $color->getIlluminant();
        $observer = $color->getObserver();
        $originalSpaceName = $color->getColorSpaceName();
        $originalSpaceClass = $color->getColorSpace();

        $triadic = match ($method) {
            TriadicMethod::DisplayAccurate => $this->triadicDisplayAccurate($color, $illuminant, $observer),
            TriadicMethod::Artistic => $this->triadicArtistic($color, $illuminant, $observer),
            TriadicMethod::Perceptual => $this->triadicPerceptual($color, $illuminant, $observer),
        };

        $result = $this->convertToOriginalSpace($triadic, $originalSpaceName);

        if ($originalSpaceClass::supportAlphaChannel()) {
            $alphaChannel = $originalSpaceClass::getAlphaChannelName();
            if ($alphaChannel !== '' && in_array($alphaChannel, $color->getChannels(), true)) {
                $result = $result->with([$alphaChannel => $color->getChannel($alphaChannel)]);
            }
        }

        return $result;
    }

    private function triadicDisplayAccurate(ColorInterface $color, $illuminant, $observer): Color
    {
        $hsv = $color->toHSV();
        $h = $hsv->getChannel('h');
        $s = $hsv->getChannel('s');
        $v = $hsv->getChannel('v');
        $h2 = fmod($h + (float) $this->hueOffset, 360.0);
        if ($h2 < 0) {
            $h2 += 360.0;
        }
        $triadicHsv = new Color(HSV::class, ['h' => $h2, 's' => $s, 'v' => $v], $illuminant, $observer);
        return $triadicHsv->toRGB();
    }

    private function triadicArtistic(ColorInterface $color, $illuminant, $observer): Color
    {
        $hsl = $color->toHSL();
        $h = $hsl->getChannel('h');
        $s = $hsl->getChannel('s');
        $l = $hsl->getChannel('l');
        $h2 = fmod($h + (float) $this->hueOffset, 360.0);
        if ($h2 < 0) {
            $h2 += 360.0;
        }
        return new Color(HSL::class, ['h' => $h2, 's' => $s, 'l' => $l], $illuminant, $observer);
    }

    private function triadicPerceptual(ColorInterface $color, $illuminant, $observer): Color
    {
        $lch = $color->toLCh();
        $l = $lch->getChannel('l');
        $c = $lch->getChannel('c');
        $h = $lch->getChannel('h');
        $h2 = fmod($h + (float) $this->hueOffset, 360.0);
        if ($h2 < 0) {
            $h2 += 360.0;
        }
        return new Color(LCh::class, ['l' => $l, 'c' => $c, 'h' => $h2], $illuminant, $observer);
    }

    private function convertToOriginalSpace(Color $triadic, string $originalSpaceName): ColorInterface
    {
        $method = 'to' . ucfirst(strtolower($originalSpaceName));
        if (!is_callable([$triadic, $method])) {
            return $triadic->toRGB();
        }
        return $triadic->$method();
    }
}

<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\ColorSpace\LCh;
use Negarity\Color\ColorSpace\HSV;

/**
 * Analogous color generator.
 *
 * Computes one of the two analogous colors (base hue - 30° or + 30°) using the chosen method:
 * - Artistic: HSL hue + offset (common in design tools).
 * - Perceptual: LCh hue + offset (perceptually uniform).
 * - DisplayAccurate: HSV hue + offset, then convert back to RGB.
 *
 * Register two instances with GeneratorRegistry for both analogous colors:
 *   GeneratorRegistry::register(new AnalogousGenerator(-30));  // analogous1
 *   GeneratorRegistry::register(new AnalogousGenerator(30));    // analogous2
 */
final class AnalogousGenerator implements GeneratorInterface
{
    private const NAME_ANALOGOUS_1 = 'analogous1';
    private const NAME_ANALOGOUS_2 = 'analogous2';

    public function __construct(
        private readonly int $hueOffset
    ) {
        if ($hueOffset !== -30 && $hueOffset !== 30) {
            throw new \InvalidArgumentException('AnalogousGenerator hue offset must be -30 or 30.');
        }
    }

    #[\Override]
    public function getName(): string
    {
        return $this->hueOffset === -30 ? self::NAME_ANALOGOUS_1 : self::NAME_ANALOGOUS_2;
    }

    #[\Override]
    public function apply(ColorInterface $color, mixed $value = null): ColorInterface
    {
        $method = AnalogousMethod::fromValue($value);
        $illuminant = $color->getIlluminant();
        $observer = $color->getObserver();
        $originalSpaceName = $color->getColorSpaceName();
        $originalSpaceClass = $color->getColorSpace();

        $analogous = match ($method) {
            AnalogousMethod::DisplayAccurate => $this->analogousDisplayAccurate($color, $illuminant, $observer),
            AnalogousMethod::Artistic => $this->analogousArtistic($color, $illuminant, $observer),
            AnalogousMethod::Perceptual => $this->analogousPerceptual($color, $illuminant, $observer),
        };

        $result = $this->convertToOriginalSpace($analogous, $originalSpaceName);

        if ($originalSpaceClass::supportAlphaChannel()) {
            $alphaChannel = $originalSpaceClass::getAlphaChannelName();
            if ($alphaChannel !== '' && in_array($alphaChannel, $color->getChannels(), true)) {
                $result = $result->with([$alphaChannel => $color->getChannel($alphaChannel)]);
            }
        }

        return $result;
    }

    private function analogousDisplayAccurate(ColorInterface $color, $illuminant, $observer): Color
    {
        $hsv = $color->toHSV();
        $h = $hsv->getChannel('h');
        $s = $hsv->getChannel('s');
        $v = $hsv->getChannel('v');
        $h2 = fmod($h + (float) $this->hueOffset, 360.0);
        if ($h2 < 0) {
            $h2 += 360.0;
        }
        $analogousHsv = new Color(HSV::class, ['h' => $h2, 's' => $s, 'v' => $v], $illuminant, $observer);
        return $analogousHsv->toRGB();
    }

    private function analogousArtistic(ColorInterface $color, $illuminant, $observer): Color
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

    private function analogousPerceptual(ColorInterface $color, $illuminant, $observer): Color
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

    private function convertToOriginalSpace(Color $analogous, string $originalSpaceName): ColorInterface
    {
        $method = 'to' . ucfirst(strtolower($originalSpaceName));
        if (!is_callable([$analogous, $method])) {
            return $analogous->toRGB();
        }
        return $analogous->$method();
    }
}

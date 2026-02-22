<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\ColorSpace\LCh;
use Negarity\Color\ColorSpace\HSV;

/**
 * Split-complementary color generator.
 *
 * Computes one of the two split-complementary colors (base hue + 150° or + 210°, i.e. ±30° from complement).
 *
 * - Artistic: HSL hue + 150° or 210° (common in design tools).
 * - Perceptual: LCh hue + 150° or 210° (perceptually uniform).
 * - DisplayAccurate: HSV hue + 150° or 210°, then convert back to RGB.
 *
 * Register two instances with GeneratorRegistry:
 *   GeneratorRegistry::register(new SplitComplementaryGenerator(150));  // split_complementary1
 *   GeneratorRegistry::register(new SplitComplementaryGenerator(210));   // split_complementary2
 */
final class SplitComplementaryGenerator implements GeneratorInterface
{
    private const NAME_SPLIT_1 = 'split_complementary1';
    private const NAME_SPLIT_2 = 'split_complementary2';

    public function __construct(
        private readonly int $hueOffset
    ) {
        if ($hueOffset !== 150 && $hueOffset !== 210) {
            throw new \InvalidArgumentException('SplitComplementaryGenerator hue offset must be 150 or 210.');
        }
    }

    #[\Override]
    public function getName(): string
    {
        return $this->hueOffset === 150 ? self::NAME_SPLIT_1 : self::NAME_SPLIT_2;
    }

    #[\Override]
    public function apply(ColorInterface $color, mixed $value = null): ColorInterface
    {
        $method = SplitComplementaryMethod::fromValue($value);
        $illuminant = $color->getIlluminant();
        $observer = $color->getObserver();
        $originalSpaceName = $color->getColorSpaceName();
        $originalSpaceClass = $color->getColorSpace();

        $resultColor = match ($method) {
            SplitComplementaryMethod::DisplayAccurate => $this->splitDisplayAccurate($color, $illuminant, $observer),
            SplitComplementaryMethod::Artistic => $this->splitArtistic($color, $illuminant, $observer),
            SplitComplementaryMethod::Perceptual => $this->splitPerceptual($color, $illuminant, $observer),
        };

        $result = $this->convertToOriginalSpace($resultColor, $originalSpaceName);

        if ($originalSpaceClass::supportAlphaChannel()) {
            $alphaChannel = $originalSpaceClass::getAlphaChannelName();
            if ($alphaChannel !== '' && in_array($alphaChannel, $color->getChannels(), true)) {
                $result = $result->with([$alphaChannel => $color->getChannel($alphaChannel)]);
            }
        }

        return $result;
    }

    private function splitDisplayAccurate(ColorInterface $color, $illuminant, $observer): Color
    {
        $hsv = $color->toHSV();
        $h = $hsv->getChannel('h');
        $s = $hsv->getChannel('s');
        $v = $hsv->getChannel('v');
        $h2 = fmod($h + (float) $this->hueOffset, 360.0);
        if ($h2 < 0) {
            $h2 += 360.0;
        }
        $splitHsv = new Color(HSV::class, ['h' => $h2, 's' => $s, 'v' => $v], $illuminant, $observer);
        return $splitHsv->toRGB();
    }

    private function splitArtistic(ColorInterface $color, $illuminant, $observer): Color
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

    private function splitPerceptual(ColorInterface $color, $illuminant, $observer): Color
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

    private function convertToOriginalSpace(Color $split, string $originalSpaceName): ColorInterface
    {
        $method = 'to' . ucfirst(strtolower($originalSpaceName));
        if (!is_callable([$split, $method])) {
            return $split->toRGB();
        }
        return $split->$method();
    }
}

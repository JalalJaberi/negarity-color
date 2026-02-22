<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\ColorSpace\LCh;
use Negarity\Color\ColorSpace\HSV;

/**
 * Monochromatic tint generator (lighten: add white).
 *
 * For count N, produces step 1..N-1 tints. apply() value: method string or array ['method'=>'perceptual','count'=>4]. Default count 4.
 */
final class MonochromaticTintGenerator implements GeneratorInterface
{
    private const DEFAULT_COUNT = 4;
    private const MAX_STEP = 9;

    public function __construct(
        private readonly int $step
    ) {
        if ($this->step < 1 || $this->step > self::MAX_STEP) {
            throw new \InvalidArgumentException('MonochromaticTintGenerator step must be 1 to ' . self::MAX_STEP . '.');
        }
    }

    #[\Override]
    public function getName(): string
    {
        return 'mono_tint_' . $this->step;
    }

    #[\Override]
    public function apply(ColorInterface $color, mixed $value = null): ColorInterface
    {
        [$method, $count] = $this->parseValue($value);
        $count = max(2, min(10, $count));
        if ($this->step >= $count) {
            return $color;
        }
        $t = $this->step / (float) $count;
        $illuminant = $color->getIlluminant();
        $observer = $color->getObserver();
        $originalSpaceName = $color->getColorSpaceName();
        $originalSpaceClass = $color->getColorSpace();

        $result = match ($method) {
            MonochromaticMethod::Artistic => $this->tintArtistic($color, $t, $illuminant, $observer),
            MonochromaticMethod::Perceptual => $this->tintPerceptual($color, $t, $illuminant, $observer),
            MonochromaticMethod::DisplayAccurate => $this->tintDisplayAccurate($color, $t, $illuminant, $observer),
        };

        $result = $this->convertToOriginalSpace($result, $originalSpaceName);
        if ($originalSpaceClass::supportAlphaChannel()) {
            $alphaChannel = $originalSpaceClass::getAlphaChannelName();
            if ($alphaChannel !== '' && in_array($alphaChannel, $color->getChannels(), true)) {
                $result = $result->with([$alphaChannel => $color->getChannel($alphaChannel)]);
            }
        }
        return $result;
    }

    private function parseValue(mixed $value): array
    {
        $method = MonochromaticMethod::Perceptual;
        $count = self::DEFAULT_COUNT;
        if (is_array($value)) {
            $method = MonochromaticMethod::fromValue($value['method'] ?? 'perceptual');
            $count = (int) ($value['count'] ?? $count);
        } else {
            $method = MonochromaticMethod::fromValue($value);
        }
        return [$method, $count];
    }

    private function tintArtistic(ColorInterface $color, float $t, $illuminant, $observer): Color
    {
        $hsl = $color->toHSL();
        $h = $hsl->getChannel('h');
        $s = $hsl->getChannel('s');
        $l = $hsl->getChannel('l');
        $lNew = min(100, $l + (100 - $l) * $t);
        return new Color(HSL::class, ['h' => $h, 's' => $s, 'l' => $lNew], $illuminant, $observer);
    }

    private function tintPerceptual(ColorInterface $color, float $t, $illuminant, $observer): Color
    {
        $lch = $color->toLCh();
        $l = $lch->getChannel('l');
        $c = $lch->getChannel('c');
        $h = $lch->getChannel('h');
        $lNew = min(100, $l + (100 - $l) * $t);
        return new Color(LCh::class, ['l' => $lNew, 'c' => $c, 'h' => $h], $illuminant, $observer);
    }

    private function tintDisplayAccurate(ColorInterface $color, float $t, $illuminant, $observer): Color
    {
        $hsv = $color->toHSV();
        $h = $hsv->getChannel('h');
        $s = $hsv->getChannel('s');
        $v = $hsv->getChannel('v');
        $vNew = min(100, $v + (100 - $v) * $t);
        $out = new Color(HSV::class, ['h' => $h, 's' => $s, 'v' => $vNew], $illuminant, $observer);
        return $out->toRGB();
    }

    private function convertToOriginalSpace(Color $c, string $originalSpaceName): ColorInterface
    {
        $method = 'to' . ucfirst(strtolower($originalSpaceName));
        if (!is_callable([$c, $method])) {
            return $c->toRGB();
        }
        return $c->$method();
    }
}

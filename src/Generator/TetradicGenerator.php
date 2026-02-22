<?php

declare(strict_types=1);

namespace Negarity\Color\Generator;

use Negarity\Color\Color;
use Negarity\Color\ColorInterface;
use Negarity\Color\ColorSpace\LCh;

/**
 * Tetradic color generator.
 *
 * Computes one of the three tetradic colors from a base using either:
 * - Square: base + 90°, + 180°, + 270° (four colors 90° apart).
 * - Rectangle: base + 30°, + 180°, + 210° (rectangular tetradic).
 *
 * Hue shift is applied in LCh (perceptually uniform). Register three instances:
 *   GeneratorRegistry::register(new TetradicGenerator(90, 30));   // tetradic1
 *   GeneratorRegistry::register(new TetradicGenerator(180, 180)); // tetradic2
 *   GeneratorRegistry::register(new TetradicGenerator(270, 210));  // tetradic3
 */
final class TetradicGenerator implements GeneratorInterface
{
    private const NAME_TETRADIC_1 = 'tetradic1';
    private const NAME_TETRADIC_2 = 'tetradic2';
    private const NAME_TETRADIC_3 = 'tetradic3';

    public function __construct(
        private readonly int $offsetSquare,
        private readonly int $offsetRectangle
    ) {
        $validSquare = [90, 180, 270];
        $validRectangle = [30, 180, 210];
        if (!in_array($this->offsetSquare, $validSquare, true) || !in_array($this->offsetRectangle, $validRectangle, true)) {
            throw new \InvalidArgumentException(
                'TetradicGenerator: Square offsets must be 90, 180, or 270; Rectangle must be 30, 180, or 210.'
            );
        }
    }

    #[\Override]
    public function getName(): string
    {
        return match ($this->offsetSquare) {
            90 => self::NAME_TETRADIC_1,
            180 => self::NAME_TETRADIC_2,
            270 => self::NAME_TETRADIC_3,
            default => self::NAME_TETRADIC_1,
        };
    }

    #[\Override]
    public function apply(ColorInterface $color, mixed $value = null): ColorInterface
    {
        $method = TetradicMethod::fromValue($value);
        $offset = $method === TetradicMethod::Square ? $this->offsetSquare : $this->offsetRectangle;
        $illuminant = $color->getIlluminant();
        $observer = $color->getObserver();
        $originalSpaceName = $color->getColorSpaceName();
        $originalSpaceClass = $color->getColorSpace();

        $tetradic = $this->tetradicPerceptual($color, (float) $offset, $illuminant, $observer);
        $result = $this->convertToOriginalSpace($tetradic, $originalSpaceName);

        if ($originalSpaceClass::supportAlphaChannel()) {
            $alphaChannel = $originalSpaceClass::getAlphaChannelName();
            if ($alphaChannel !== '' && in_array($alphaChannel, $color->getChannels(), true)) {
                $result = $result->with([$alphaChannel => $color->getChannel($alphaChannel)]);
            }
        }

        return $result;
    }

    private function tetradicPerceptual(ColorInterface $color, float $offset, $illuminant, $observer): Color
    {
        $lch = $color->toLCh();
        $l = $lch->getChannel('l');
        $c = $lch->getChannel('c');
        $h = $lch->getChannel('h');
        $h2 = fmod($h + $offset, 360.0);
        if ($h2 < 0) {
            $h2 += 360.0;
        }
        return new Color(LCh::class, ['l' => $l, 'c' => $c, 'h' => $h2], $illuminant, $observer);
    }

    private function convertToOriginalSpace(Color $tetradic, string $originalSpaceName): ColorInterface
    {
        $method = 'to' . ucfirst(strtolower($originalSpaceName));
        if (!is_callable([$tetradic, $method])) {
            return $tetradic->toRGB();
        }
        return $tetradic->$method();
    }
}

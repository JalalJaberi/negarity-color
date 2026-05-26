<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use Negarity\Color\Color;
use Negarity\Color\Extractor\SaturationExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;
use PHPUnit\Framework\TestCase;

final class SaturationExtractorTest extends TestCase
{
    private SaturationExtractor $extractor;

    protected function setUp(): void
    {
        ColorSpaceRegistry::registerBuiltIn();
        $this->extractor = new SaturationExtractor();
    }

    public function testWhiteAndBlackAreAchromaticForAllAlgorithms(): void
    {
        $white = Color::rgb(255, 255, 255);
        $black = Color::rgb(0, 0, 0);

        foreach ([$white, $black] as $color) {
            self::assertLessThan(1.0, $this->extractor->extract($color));
            self::assertLessThan(1.0, $this->extractor->extract($color, [
                'algorithm' => SaturationExtractor::ALGORITHM_HSL,
            ]));
        }
    }

    public function testPrimaryRedIsFullySaturatedInHsv(): void
    {
        $red = Color::rgb(255, 0, 0);

        self::assertEqualsWithDelta(100.0, $this->extractor->extract($red), 0.01);
    }

    public function testDefaultAlgorithmIsHsv(): void
    {
        self::assertSame(
            SaturationExtractor::ALGORITHM_HSV,
            SaturationExtractor::resolveAlgorithm(null)
        );
    }

    public function testHsvMatchesToHsvChannel(): void
    {
        $lime = Color::rgb(0, 255, 0);

        self::assertEqualsWithDelta(
            $lime->toHSV()->getChannel('s'),
            $this->extractor->extract($lime),
            0.01
        );
    }

    public function testHslAlgorithmMatchesToHslChannel(): void
    {
        $blue = Color::rgb(0, 0, 255);

        self::assertEqualsWithDelta(
            $blue->toHSL()->getChannel('s'),
            $this->extractor->extract($blue, ['algorithm' => SaturationExtractor::ALGORITHM_HSL]),
            0.01
        );
    }

    public function testHsvAndHslDifferForMidLightnessColors(): void
    {
        $pastel = Color::rgb(255, 200, 210);

        $hsv = $this->extractor->extract($pastel);
        $hsl = $this->extractor->extract($pastel, ['algorithm' => SaturationExtractor::ALGORITHM_HSL]);

        self::assertNotEqualsWithDelta($hsv, $hsl, 1.0);
    }
}

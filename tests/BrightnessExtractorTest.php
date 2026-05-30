<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use Negarity\Color\Color;
use Negarity\Color\CIE\CieCamAppearance;
use Negarity\Color\Extractor\BrightnessExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;
use PHPUnit\Framework\TestCase;

final class BrightnessExtractorTest extends TestCase
{
    private BrightnessExtractor $extractor;

    protected function setUp(): void
    {
        ColorSpaceRegistry::registerBuiltIn();
        $this->extractor = new BrightnessExtractor();
    }

    public function testDefaultAlgorithmIsLch(): void
    {
        self::assertSame(
            BrightnessExtractor::ALGORITHM_LCH,
            BrightnessExtractor::resolveAlgorithm(null)
        );

        $white = Color::rgb(255, 255, 255);
        self::assertEqualsWithDelta(
            $white->toLCh()->getChannel('l'),
            $this->extractor->extract($white),
            0.01
        );
    }

    public function testWhiteIsBrightForHeuristicAlgorithms(): void
    {
        $white = Color::rgb(255, 255, 255);

        self::assertGreaterThan(99.0, $this->extractor->extract($white, [
            'algorithm' => BrightnessExtractor::ALGORITHM_AVERAGE,
        ]));
        self::assertGreaterThan(99.0, $this->extractor->extract($white, [
            'algorithm' => BrightnessExtractor::ALGORITHM_HSV_VALUE,
        ]));
        self::assertGreaterThan(99.0, $this->extractor->extract($white, [
            'algorithm' => BrightnessExtractor::ALGORITHM_REC709,
        ]));
    }

    public function testBlackIsDarkForHeuristicAlgorithms(): void
    {
        $black = Color::rgb(0, 0, 0);

        self::assertLessThan(1.0, $this->extractor->extract($black, [
            'algorithm' => BrightnessExtractor::ALGORITHM_AVERAGE,
        ]));
        self::assertLessThan(1.0, $this->extractor->extract($black, [
            'algorithm' => BrightnessExtractor::ALGORITHM_REC601,
        ]));
    }

    public function testAverageFormula(): void
    {
        $gray = Color::rgb(120, 120, 120);
        $expected = (120.0 / 255.0) * 100.0;

        self::assertEqualsWithDelta(
            $expected,
            $this->extractor->extract($gray, ['algorithm' => BrightnessExtractor::ALGORITHM_AVERAGE]),
            0.01
        );
    }

    public function testLightnessMaxMinMidpoint(): void
    {
        $color = Color::rgb(100, 200, 50);
        $expected = ((200.0 + 50.0) / 2.0 / 255.0) * 100.0;

        self::assertEqualsWithDelta(
            $expected,
            $this->extractor->extract($color, ['algorithm' => BrightnessExtractor::ALGORITHM_LIGHTNESS]),
            0.01
        );
    }

    public function testHsvValueMatchesHsvChannel(): void
    {
        $red = Color::rgb(255, 0, 0);

        self::assertEqualsWithDelta(
            $red->toHSV()->getChannel('v'),
            $this->extractor->extract($red, ['algorithm' => BrightnessExtractor::ALGORITHM_HSV_VALUE]),
            0.01
        );
    }

    public function testRec709OnGammaEncodedRgb(): void
    {
        $yellow = Color::rgb(255, 255, 0);
        $expected = ((0.2126 * 255.0 + 0.7152 * 255.0) / 255.0) * 100.0;

        self::assertEqualsWithDelta(
            $expected,
            $this->extractor->extract($yellow, ['algorithm' => BrightnessExtractor::ALGORITHM_REC709]),
            0.01
        );
    }

    public function testLabLStarMatchesLabChannel(): void
    {
        $blue = Color::rgb(0, 0, 255);

        self::assertEqualsWithDelta(
            $blue->toLab()->getChannel('l'),
            $this->extractor->extract($blue, ['algorithm' => BrightnessExtractor::ALGORITHM_CIE1976_LAB]),
            0.01
        );
    }

    public function testCieCam16ReferenceLightness(): void
    {
        $j = CieCamAppearance::lightnessJ(
            ['x' => 19.01, 'y' => 20.00, 'z' => 21.78],
            ['x' => 95.05, 'y' => 100.00, 'z' => 108.88],
            'ciecam16',
            ['L_A' => 318.31, 'Y_b' => 20.0],
        );

        self::assertEqualsWithDelta(41.731, $j, 0.05);
    }

    public function testCieCam02AndCam16ProduceReasonableRange(): void
    {
        $mid = Color::rgb(128, 64, 192);

        $j02 = $this->extractor->extract($mid, ['algorithm' => BrightnessExtractor::ALGORITHM_CIECAM02]);
        $j16 = $this->extractor->extract($mid, ['algorithm' => BrightnessExtractor::ALGORITHM_CIECAM16]);

        self::assertGreaterThan(0.0, $j02);
        self::assertLessThan(100.0, $j02);
        self::assertGreaterThan(0.0, $j16);
        self::assertLessThan(100.0, $j16);
    }
}

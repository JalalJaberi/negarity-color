<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use Negarity\Color\Color;
use Negarity\Color\Extractor\PerceivedWeightExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;
use PHPUnit\Framework\TestCase;

final class PerceivedWeightExtractorTest extends TestCase
{
    private PerceivedWeightExtractor $extractor;

    protected function setUp(): void
    {
        ColorSpaceRegistry::registerBuiltIn();
        $this->extractor = new PerceivedWeightExtractor();
    }

    public function testDefaultAlgorithmIsLinear(): void
    {
        self::assertSame(
            PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_LINEAR,
            PerceivedWeightExtractor::resolveAlgorithm(null)
        );
    }

    public function testWhiteIsLightForBothAlgorithms(): void
    {
        $white = Color::rgb(255, 255, 255);

        self::assertLessThan(5.0, $this->extractor->extract($white));
        self::assertLessThan(5.0, $this->extractor->extract($white, [
            'algorithm' => PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION,
        ]));
    }

    public function testBlackIsHeavyForBothAlgorithms(): void
    {
        $black = Color::rgb(0, 0, 0);

        self::assertEqualsWithDelta(70.0, $this->extractor->extract($black), 0.01);
        self::assertGreaterThan(95.0, $this->extractor->extract($black, [
            'algorithm' => PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION,
        ]));
    }

    public function testLinearMatchesLegacyFormula(): void
    {
        $red = Color::rgb(255, 0, 0);
        $lch = $red->toLCh();
        $darkness = 100.0 - $lch->getChannel('l');
        $chromaNorm = min(1.0, $lch->getChannel('c') / 130.0);
        $expected = min(100.0, max(0.0, $darkness * 0.7 + $chromaNorm * 100.0 * 0.3));

        self::assertEqualsWithDelta(
            $expected,
            $this->extractor->extract($red),
            0.01
        );
    }

    public function testMultiplicationFormula(): void
    {
        $red = Color::rgb(255, 0, 0);
        $lch = $red->toLCh();
        $darkness = 100.0 - $lch->getChannel('l');
        $chromaNorm = min(1.0, $lch->getChannel('c') / 130.0);
        $expected = min(100.0, max(0.0, $darkness * (1.0 + 0.5 * $chromaNorm)));

        self::assertEqualsWithDelta(
            $expected,
            $this->extractor->extract($red, [
                'algorithm' => PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION,
            ]),
            0.01
        );
    }

    public function testSaturatedDarkRedIsHeavierWithMultiplicationThanLinear(): void
    {
        $darkRed = Color::rgb(128, 0, 0);
        $linear = $this->extractor->extract($darkRed);
        $multiplication = $this->extractor->extract($darkRed, [
            'algorithm' => PerceivedWeightExtractor::ALGORITHM_BRIGHTNESS_CHROMA_MULTIPLICATION,
        ]);

        self::assertGreaterThan($linear, $multiplication);
    }
}

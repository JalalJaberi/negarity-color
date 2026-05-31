<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use Negarity\Color\Color;
use Negarity\Color\Extractor\VibrancyExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;
use PHPUnit\Framework\TestCase;

final class VibrancyExtractorTest extends TestCase
{
    private VibrancyExtractor $extractor;

    protected function setUp(): void
    {
        ColorSpaceRegistry::registerBuiltIn();
        $this->extractor = new VibrancyExtractor();
    }

    public function testDefaultAlgorithmIsMidtoneChromaIndex(): void
    {
        self::assertSame(
            VibrancyExtractor::ALGORITHM_MIDTONE_CHROMA_INDEX,
            VibrancyExtractor::resolveAlgorithm(null)
        );
    }

    public function testAchromaticColorsHaveZeroVibrancy(): void
    {
        $gray = Color::rgb(128, 128, 128);

        self::assertLessThan(1.0, $this->extractor->extract($gray));
        self::assertLessThan(1.0, $this->extractor->extract($gray, [
            'algorithm' => VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX,
        ]));
    }

    public function testMidtoneChromaMatchesLegacyFormula(): void
    {
        $red = Color::rgb(255, 0, 0);
        $lch = $red->toLCh();
        $chromaNorm = min(1.0, $lch->getChannel('c') / 130.0);
        $midPeak = 1.0 - 2.0 * abs($lch->getChannel('l') / 100.0 - 0.5);
        $expected = min(100.0, max(0.0, $chromaNorm * max(0.0, $midPeak) * 100.0));

        self::assertEqualsWithDelta(
            $expected,
            $this->extractor->extract($red),
            0.01
        );
    }

    public function testGaussianFormula(): void
    {
        $red = Color::rgb(255, 0, 0);
        $lch = $red->toLCh();
        $l = $lch->getChannel('l');
        $chromaNorm = min(1.0, $lch->getChannel('c') / 130.0);
        $mu = 50.0;
        $sigma = 25.0;
        $envelope = exp(-(($l - $mu) ** 2) / (2.0 * $sigma * $sigma));
        $expected = min(100.0, max(0.0, $chromaNorm * $envelope * 100.0));

        self::assertEqualsWithDelta(
            $expected,
            $this->extractor->extract($red, [
                'algorithm' => VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX,
            ]),
            0.01
        );
    }

    public function testPureRedIsMoreVibrantWithGaussianThanMidtoneAtExtremeLightness(): void
    {
        $pastel = Color::rgb(255, 200, 200);
        $midtone = $this->extractor->extract($pastel);
        $gaussian = $this->extractor->extract($pastel, [
            'algorithm' => VibrancyExtractor::ALGORITHM_GAUSSIAN_VIBRANCY_INDEX,
        ]);

        self::assertGreaterThan($midtone, $gaussian);
    }

    public function testMidLightSaturatedColorIsVibrant(): void
    {
        $red = Color::rgb(255, 0, 0);
        $value = $this->extractor->extract($red);

        self::assertGreaterThan(40.0, $value);
    }
}

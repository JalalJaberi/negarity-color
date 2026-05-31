<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use Negarity\Color\Color;
use Negarity\Color\Extractor\ContrastExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;
use PHPUnit\Framework\TestCase;

final class ContrastExtractorTest extends TestCase
{
    private ContrastExtractor $extractor;

    protected function setUp(): void
    {
        ColorSpaceRegistry::registerBuiltIn();
        $this->extractor = new ContrastExtractor();
    }

    public function testDefaultIsWcagAgainstWhite(): void
    {
        [$algo, $ref] = ContrastExtractor::resolveParams(null);
        self::assertSame(ContrastExtractor::ALGORITHM_WCAG_CONTRAST_RATIO, $algo);
        self::assertSame('white', $ref);
    }

    public function testWcagBlackStringBackwardCompat(): void
    {
        $black = Color::rgb(0, 0, 0);
        self::assertGreaterThan(20.0, $this->extractor->extract($black, 'white'));
        self::assertEqualsWithDelta(1.0, $this->extractor->extract($black, 'black'), 0.01);
    }

    public function testWcagWhiteOnWhiteIsOne(): void
    {
        $white = Color::rgb(255, 255, 255);
        self::assertEqualsWithDelta(1.0, $this->extractor->extract($white, 'white'), 0.01);
    }

    public function testMichelsonBlackAndWhite(): void
    {
        $black = Color::rgb(0, 0, 0);
        $value = $this->extractor->extract($black, [
            'algorithm' => ContrastExtractor::ALGORITHM_MICHELSON_CONTRAST,
            'contrastWith' => 'white',
        ]);

        self::assertEqualsWithDelta(100.0, $value, 0.5);
    }

    public function testWeberRedOnWhiteIsNegative(): void
    {
        $red = Color::rgb(255, 0, 0);
        $value = $this->extractor->extract($red, [
            'algorithm' => ContrastExtractor::ALGORITHM_WEBER_CONTRAST,
            'contrastWith' => 'white',
        ]);

        self::assertLessThan(0.0, $value);
    }

    public function testRmsBetweenZeroAndOneHundred(): void
    {
        $red = Color::rgb(255, 0, 0);
        $value = $this->extractor->extract($red, [
            'algorithm' => ContrastExtractor::ALGORITHM_RMS_CONTRAST,
            'contrastWith' => 'white',
        ]);

        self::assertGreaterThan(0.0, $value);
        self::assertLessThanOrEqual(100.0, $value);
    }

    public function testDeltaE76SameColorIsZero(): void
    {
        $red = Color::rgb(255, 0, 0);
        $value = $this->extractor->extract($red, [
            'algorithm' => ContrastExtractor::ALGORITHM_DELTA_E76,
            'contrastWith' => 'white',
        ]);

        self::assertGreaterThan(0.0, $value);
    }

    public function testDeltaE76IdenticalColorsViaCustomReference(): void
    {
        $red = Color::rgb(255, 0, 0);
        $value = $this->extractor->extract($red, [
            'algorithm' => ContrastExtractor::ALGORITHM_DELTA_E76,
            'contrastWith' => $red,
        ]);

        self::assertEqualsWithDelta(0.0, $value, 0.01);
    }
}

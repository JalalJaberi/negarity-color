<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use Negarity\Color\Color;
use Negarity\Color\Extractor\ChromaExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;
use PHPUnit\Framework\TestCase;

final class ChromaExtractorTest extends TestCase
{
    private ChromaExtractor $extractor;

    protected function setUp(): void
    {
        ColorSpaceRegistry::registerBuiltIn();
        $this->extractor = new ChromaExtractor();
    }

    public function testWhiteIsNeutralForAllAlgorithms(): void
    {
        $white = Color::rgb(255, 255, 255);

        self::assertLessThan(5.0, $this->extractor->extract($white));
        self::assertLessThan(5.0, $this->extractor->extract($white, [
            'algorithm' => ChromaExtractor::ALGORITHM_CIE1976_LAB,
        ]));
        self::assertLessThan(5.0, $this->extractor->extract($white, [
            'algorithm' => ChromaExtractor::ALGORITHM_CIE1976_LUV,
        ]));
    }

    public function testRedIsHighChroma(): void
    {
        $red = Color::rgb(255, 0, 0);

        self::assertGreaterThan(50.0, $this->extractor->extract($red));
        self::assertGreaterThan(50.0, $this->extractor->extract($red, [
            'algorithm' => ChromaExtractor::ALGORITHM_CIE1976_LAB,
        ]));
    }

    public function testDefaultAlgorithmIsOklch(): void
    {
        self::assertSame(
            ChromaExtractor::ALGORITHM_OKLCH,
            ChromaExtractor::resolveAlgorithm(null)
        );
    }

    public function testOklchMatchesHypotFromOklab(): void
    {
        $red = Color::rgb(255, 0, 0);
        $oklab = $red->toOklab();
        $a = $oklab->getChannel('a');
        $b = $oklab->getChannel('b');
        $expected = min(100.0, (sqrt($a * $a + $b * $b) / 0.4) * 100.0);

        self::assertEqualsWithDelta($expected, $this->extractor->extract($red), 0.01);
    }

    public function testLabAlgorithmMatchesLegacyFormula(): void
    {
        $red = Color::rgb(255, 0, 0);
        $lab = $red->toLab();
        $a = $lab->getChannel('a');
        $b = $lab->getChannel('b');
        $expected = min(100.0, (sqrt($a * $a + $b * $b) / 150.0) * 100.0);

        self::assertEqualsWithDelta(
            $expected,
            $this->extractor->extract($red, ['algorithm' => ChromaExtractor::ALGORITHM_CIE1976_LAB]),
            0.01
        );
    }

    public function testToOklchAndToOklabAreRegistered(): void
    {
        $red = Color::rgb(255, 0, 0);
        $oklch = $red->toOklch();
        $oklab = $red->toOklab();

        self::assertEqualsWithDelta(
            sqrt(
                $oklab->getChannel('a') ** 2 + $oklab->getChannel('b') ** 2
            ),
            $oklch->getChannel('c'),
            0.0001
        );
    }

    public function testOklchFromHslSourceColor(): void
    {
        $hsl = Color::hsl(120, 80, 45);

        self::assertGreaterThan(0.0, $this->extractor->extract($hsl));
        self::assertNotNull($hsl->toOklab());
    }
}

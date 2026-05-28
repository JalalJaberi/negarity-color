<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use Negarity\Color\Color;
use Negarity\Color\Extractor\LuminanceExtractor;
use Negarity\Color\Registry\ColorSpaceRegistry;
use PHPUnit\Framework\TestCase;

final class LuminanceExtractorTest extends TestCase
{
    private LuminanceExtractor $extractor;

    protected function setUp(): void
    {
        ColorSpaceRegistry::registerBuiltIn();
        $this->extractor = new LuminanceExtractor();
    }

    public function testBlackIsZero(): void
    {
        self::assertEqualsWithDelta(0.0, $this->extractor->extract(Color::rgb(0, 0, 0)), 0.01);
    }

    public function testWhiteIsNearOneHundred(): void
    {
        self::assertGreaterThan(95.0, $this->extractor->extract(Color::rgb(255, 255, 255)));
    }

    public function testMatchesXyzYChannel(): void
    {
        $green = Color::rgb(0, 255, 0);

        self::assertEqualsWithDelta(
            $green->toXYZ()->getChannel('y'),
            $this->extractor->extract($green),
            0.01
        );
    }

    public function testGreenIsBrighterThanBlue(): void
    {
        $green = $this->extractor->extract(Color::rgb(0, 255, 0));
        $blue = $this->extractor->extract(Color::rgb(0, 0, 255));

        self::assertGreaterThan($blue, $green);
    }

    public function testYellowHasHigherLuminanceThanBlue(): void
    {
        $yellow = $this->extractor->extract(Color::rgb(255, 255, 0));
        $blue = $this->extractor->extract(Color::rgb(0, 0, 255));

        self::assertGreaterThan($blue, $yellow);
    }
}

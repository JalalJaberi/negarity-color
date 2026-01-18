<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use PHPUnit\Framework\TestCase;
use Negarity\Color\Color;
use Negarity\Color\ColorSpace\RGB;
use Negarity\Color\ColorSpace\CMYK;
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\ColorSpace\HSV;
use Negarity\Color\ColorSpace\Lab;
use Negarity\Color\ColorSpace\LCh;
use Negarity\Color\ColorSpace\XYZ;
use Negarity\Color\ColorSpace\YCbCr;

final class ColorConversionTest extends TestCase
{
    // ========== Color Conversion Tests ==========

    public function testConvertRgbToRgb(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $original = Color::rgb($r, $g, $b);
        $converted = $original->toRGB();
        
        $this->assertEquals(RGB::getName(), $converted->getColorSpaceName());
        $this->assertEquals($r, $converted->getR());
        $this->assertEquals($g, $converted->getG());
        $this->assertEquals($b, $converted->getB());
    }

    public function testConvertRgbToCmyk(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $cmyk = $rgb->toCMYK();
        
        $this->assertEquals(CMYK::getName(), $cmyk->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $cmyk->getC());
        $this->assertLessThanOrEqual(100, $cmyk->getC());
        $this->assertGreaterThanOrEqual(0, $cmyk->getM());
        $this->assertLessThanOrEqual(100, $cmyk->getM());
        $this->assertGreaterThanOrEqual(0, $cmyk->getY());
        $this->assertLessThanOrEqual(100, $cmyk->getY());
        $this->assertGreaterThanOrEqual(0, $cmyk->getK());
        $this->assertLessThanOrEqual(100, $cmyk->getK());
    }

    public function testConvertCmykToRgb(): void
    {
        $c = random_int(0, 100);
        $m = random_int(0, 100);
        $y = random_int(0, 100);
        $k = random_int(0, 100);
        $cmyk = Color::cmyk($c, $m, $y, $k);
        $rgb = $cmyk->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $rgb->getR());
        $this->assertLessThanOrEqual(255, $rgb->getR());
        $this->assertGreaterThanOrEqual(0, $rgb->getG());
        $this->assertLessThanOrEqual(255, $rgb->getG());
        $this->assertGreaterThanOrEqual(0, $rgb->getB());
        $this->assertLessThanOrEqual(255, $rgb->getB());
    }

    public function testConvertRgbToHsl(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $hsl = $rgb->toHSL();
        
        $this->assertEquals(HSL::getName(), $hsl->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $hsl->getH());
        $this->assertLessThanOrEqual(360, $hsl->getH());
        $this->assertGreaterThanOrEqual(0, $hsl->getS());
        $this->assertLessThanOrEqual(100, $hsl->getS());
        $this->assertGreaterThanOrEqual(0, $hsl->getL());
        $this->assertLessThanOrEqual(100, $hsl->getL());
    }

    public function testConvertHslToRgb(): void
    {
        $h = random_int(0, 360);
        $s = random_int(0, 100);
        $l = random_int(0, 100);
        $hsl = Color::hsl($h, $s, $l);
        $rgb = $hsl->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $rgb->getR());
        $this->assertLessThanOrEqual(255, $rgb->getR());
        $this->assertGreaterThanOrEqual(0, $rgb->getG());
        $this->assertLessThanOrEqual(255, $rgb->getG());
        $this->assertGreaterThanOrEqual(0, $rgb->getB());
        $this->assertLessThanOrEqual(255, $rgb->getB());
    }

    public function testConvertRgbToHsv(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $hsv = $rgb->toHSV();
        
        $this->assertEquals(HSV::getName(), $hsv->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $hsv->getH());
        $this->assertLessThanOrEqual(360, $hsv->getH());
        $this->assertGreaterThanOrEqual(0, $hsv->getS());
        $this->assertLessThanOrEqual(100, $hsv->getS());
        $this->assertGreaterThanOrEqual(0, $hsv->getV());
        $this->assertLessThanOrEqual(100, $hsv->getV());
    }

    public function testConvertHsvToRgb(): void
    {
        $h = random_int(0, 360);
        $s = random_int(0, 100);
        $v = random_int(0, 100);
        $hsv = Color::hsv($h, $s, $v);
        $rgb = $hsv->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $rgb->getR());
        $this->assertLessThanOrEqual(255, $rgb->getR());
        $this->assertGreaterThanOrEqual(0, $rgb->getG());
        $this->assertLessThanOrEqual(255, $rgb->getG());
        $this->assertGreaterThanOrEqual(0, $rgb->getB());
        $this->assertLessThanOrEqual(255, $rgb->getB());
    }

    public function testConvertRgbToLab(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $lab = $rgb->toLab();
        
        $this->assertEquals(Lab::getName(), $lab->getColorSpaceName());
        // Lab values are rounded to int: L: 0-100, a: -128 to 127, b: -128 to 127
        $this->assertIsInt($lab->getL());
        $this->assertIsInt($lab->getA());
        $this->assertIsInt($lab->getB());
    }

    public function testConvertLabToRgb(): void
    {
        // Use values that will produce valid RGB (Lab has wider gamut than RGB)
        // Start with RGB and convert to Lab to ensure valid values
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $lab = $rgb->toLab();
        
        // Now convert back to RGB
        $convertedRgb = $lab->toRGB();
        
        $this->assertEquals(RGB::getName(), $convertedRgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $convertedRgb->getR());
        $this->assertLessThanOrEqual(255, $convertedRgb->getR());
        $this->assertGreaterThanOrEqual(0, $convertedRgb->getG());
        $this->assertLessThanOrEqual(255, $convertedRgb->getG());
        $this->assertGreaterThanOrEqual(0, $convertedRgb->getB());
        $this->assertLessThanOrEqual(255, $convertedRgb->getB());
    }

    public function testConvertRgbToLch(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $lch = $rgb->toLCh();
        
        $this->assertEquals(LCh::getName(), $lch->getColorSpaceName());
        // LCh values are rounded to int
        $this->assertIsInt($lch->getL());
        $this->assertIsInt($lch->getC());
        $this->assertGreaterThanOrEqual(0, $lch->getH());
        $this->assertLessThanOrEqual(360, $lch->getH());
    }

    public function testConvertLchToRgb(): void
    {
        // Use values that will produce valid RGB (LCh has wider gamut than RGB)
        // Start with RGB and convert to LCh to ensure valid values
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $lch = $rgb->toLCh();
        
        // Now convert back to RGB
        $convertedRgb = $lch->toRGB();
        
        $this->assertEquals(RGB::getName(), $convertedRgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $convertedRgb->getR());
        $this->assertLessThanOrEqual(255, $convertedRgb->getR());
        $this->assertGreaterThanOrEqual(0, $convertedRgb->getG());
        $this->assertLessThanOrEqual(255, $convertedRgb->getG());
        $this->assertGreaterThanOrEqual(0, $convertedRgb->getB());
        $this->assertLessThanOrEqual(255, $convertedRgb->getB());
    }

    public function testConvertRgbToXyz(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $xyz = $rgb->toXYZ();
        
        $this->assertEquals(XYZ::getName(), $xyz->getColorSpaceName());
        // XYZ values are rounded to int
        $this->assertIsInt($xyz->getX());
        $this->assertIsInt($xyz->getY());
        $this->assertIsInt($xyz->getZ());
    }

    public function testConvertXyzToRgb(): void
    {
        // Use values that will produce valid RGB (XYZ has wider gamut than RGB)
        // Start with RGB and convert to XYZ to ensure valid values
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $xyz = $rgb->toXYZ();
        
        // Now convert back to RGB
        $convertedRgb = $xyz->toRGB();
        
        $this->assertEquals(RGB::getName(), $convertedRgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $convertedRgb->getR());
        $this->assertLessThanOrEqual(255, $convertedRgb->getR());
        $this->assertGreaterThanOrEqual(0, $convertedRgb->getG());
        $this->assertLessThanOrEqual(255, $convertedRgb->getG());
        $this->assertGreaterThanOrEqual(0, $convertedRgb->getB());
        $this->assertLessThanOrEqual(255, $convertedRgb->getB());
    }

    public function testConvertRgbToYcbcr(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $rgb = Color::rgb($r, $g, $b);
        $ycbcr = $rgb->toYCbCr();
        
        $this->assertEquals(YCbCr::getName(), $ycbcr->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $ycbcr->getY());
        $this->assertLessThanOrEqual(100, $ycbcr->getY());
        $this->assertGreaterThanOrEqual(-128, $ycbcr->getCb());
        $this->assertLessThanOrEqual(127, $ycbcr->getCb());
        $this->assertGreaterThanOrEqual(-128, $ycbcr->getCr());
        $this->assertLessThanOrEqual(127, $ycbcr->getCr());
    }

    public function testConvertYcbcrToRgb(): void
    {
        $y = random_int(0, 100);
        $cb = random_int(-128, 127);
        $cr = random_int(-128, 127);
        $ycbcr = Color::ycbcr($y, $cb, $cr);
        $rgb = $ycbcr->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $rgb->getR());
        $this->assertLessThanOrEqual(255, $rgb->getR());
        $this->assertGreaterThanOrEqual(0, $rgb->getG());
        $this->assertLessThanOrEqual(255, $rgb->getG());
        $this->assertGreaterThanOrEqual(0, $rgb->getB());
        $this->assertLessThanOrEqual(255, $rgb->getB());
    }

    // ========== Round-trip Conversion Tests ==========

    public function testRoundTripRgbToCmykAndBack(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $original = Color::rgb($r, $g, $b);
        $cmyk = $original->toCMYK();
        $backToRgb = $cmyk->toRGB();
        
        // Allow for small rounding differences
        $this->assertEqualsWithDelta($original->getR(), $backToRgb->getR(), 1);
        $this->assertEqualsWithDelta($original->getG(), $backToRgb->getG(), 1);
        $this->assertEqualsWithDelta($original->getB(), $backToRgb->getB(), 1);
    }

    public function testRoundTripRgbToHslAndBack(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $original = Color::rgb($r, $g, $b);
        $hsl = $original->toHSL();
        $backToRgb = $hsl->toRGB();
        
        // Allow for small rounding differences (HSL conversions can have larger rounding errors)
        $this->assertEqualsWithDelta($original->getR(), $backToRgb->getR(), 2);
        $this->assertEqualsWithDelta($original->getG(), $backToRgb->getG(), 2);
        $this->assertEqualsWithDelta($original->getB(), $backToRgb->getB(), 2);
    }

    public function testRoundTripRgbToHsvAndBack(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $original = Color::rgb($r, $g, $b);
        $hsv = $original->toHSV();
        $backToRgb = $hsv->toRGB();
        
        // Allow for small rounding differences (HSV conversions can have larger rounding errors)
        $this->assertEqualsWithDelta($original->getR(), $backToRgb->getR(), 2);
        $this->assertEqualsWithDelta($original->getG(), $backToRgb->getG(), 2);
        $this->assertEqualsWithDelta($original->getB(), $backToRgb->getB(), 2);
    }

    // ========== ToHex Tests ==========

    public function testRgbToHex(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $color = Color::rgb($r, $g, $b);
        $hex = $color->toHex();
        
        $expectedHex = sprintf('#%02X%02X%02X', $r, $g, $b);
        $this->assertEquals($expectedHex, $hex);
    }

    public function testRgbaToHex(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $a = random_int(0, 255);
        $color = Color::rgba($r, $g, $b, $a);
        $hex = $color->toHex();
        
        $expectedHex = sprintf('#%02X%02X%02X%02X', $r, $g, $b, $a);
        $this->assertEquals($expectedHex, $hex);
    }

    public function testColorSpaceConversionToHex(): void
    {
        $h = random_int(0, 360);
        $s = random_int(0, 100);
        $l = random_int(0, 100);
        $hsl = Color::hsl($h, $s, $l);
        $hex = $hsl->toHex();
        
        // Should convert to RGB first, then to hex
        $this->assertStringStartsWith('#', $hex);
        $this->assertEquals(7, strlen($hex)); // #RRGGBB format
    }
}

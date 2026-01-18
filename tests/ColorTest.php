<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use PHPUnit\Framework\TestCase;
use Negarity\Color\Color;
use Negarity\Color\ColorSpace\RGB;
use Negarity\Color\ColorSpace\RGBA;
use Negarity\Color\ColorSpace\CMYK;
use Negarity\Color\ColorSpace\HSL;
use Negarity\Color\ColorSpace\HSLA;
use Negarity\Color\ColorSpace\HSV;
use Negarity\Color\ColorSpace\Lab;
use Negarity\Color\ColorSpace\LCh;
use Negarity\Color\ColorSpace\XYZ;
use Negarity\Color\ColorSpace\YCbCr;

final class ColorTest extends TestCase
{
    // ========== Color Creation Tests ==========

    public function testCreateRgbColor(): void
    {
        $color = Color::rgb(255, 100, 50);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGB::getName(), $color->getColorSpaceName());
        $this->assertEquals(255, $color->getR());
        $this->assertEquals(100, $color->getG());
        $this->assertEquals(50, $color->getB());
    }

    public function testCreateRgbaColor(): void
    {
        $color = Color::rgba(255, 100, 50, 128);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGBA::getName(), $color->getColorSpaceName());
        $this->assertEquals(255, $color->getR());
        $this->assertEquals(100, $color->getG());
        $this->assertEquals(50, $color->getB());
        $this->assertEquals(128, $color->getA());
    }

    public function testCreateCmykColor(): void
    {
        $color = Color::cmyk(100, 50, 0, 25);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(CMYK::getName(), $color->getColorSpaceName());
        $this->assertEquals(100, $color->getC());
        $this->assertEquals(50, $color->getM());
        $this->assertEquals(0, $color->getY());
        $this->assertEquals(25, $color->getK());
    }

    public function testCreateHslColor(): void
    {
        $color = Color::hsl(210, 50, 40);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(HSL::getName(), $color->getColorSpaceName());
        $this->assertEquals(210, $color->getH());
        $this->assertEquals(50, $color->getS());
        $this->assertEquals(40, $color->getL());
    }

    public function testCreateHslaColor(): void
    {
        $color = Color::hsla(210, 50, 40, 128);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(HSLA::getName(), $color->getColorSpaceName());
        $this->assertEquals(210, $color->getH());
        $this->assertEquals(50, $color->getS());
        $this->assertEquals(40, $color->getL());
        $this->assertEquals(128, $color->getA());
    }

    public function testCreateHsvColor(): void
    {
        $color = Color::hsv(210, 50, 60);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(HSV::getName(), $color->getColorSpaceName());
        $this->assertEquals(210, $color->getH());
        $this->assertEquals(50, $color->getS());
        $this->assertEquals(60, $color->getV());
    }

    public function testCreateLabColor(): void
    {
        $color = Color::lab(75, 20, -30);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(Lab::getName(), $color->getColorSpaceName());
        $this->assertEquals(75, $color->getL());
        $this->assertEquals(20, $color->getA());
        $this->assertEquals(-30, $color->getB());
    }

    public function testCreateLchColor(): void
    {
        $color = Color::lch(75, 36, 210);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(LCh::getName(), $color->getColorSpaceName());
        $this->assertEquals(75, $color->getL());
        $this->assertEquals(36, $color->getC());
        $this->assertEquals(210, $color->getH());
    }

    public function testCreateXyzColor(): void
    {
        $color = Color::xyz(25, 30, 35);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(XYZ::getName(), $color->getColorSpaceName());
        $this->assertEquals(25, $color->getX());
        $this->assertEquals(30, $color->getY());
        $this->assertEquals(35, $color->getZ());
    }

    public function testCreateYcbcrColor(): void
    {
        $color = Color::ycbcr(78, 0, -100);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(YCbCr::getName(), $color->getColorSpaceName());
        $this->assertEquals(78, $color->getY());
        $this->assertEquals(0, $color->getCb());
        $this->assertEquals(-100, $color->getCr());
    }

    public function testCreateColorFromHex6Digit(): void
    {
        $color = Color::hex('#FF6432');
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGB::getName(), $color->getColorSpaceName());
        $this->assertEquals(255, $color->getR());
        $this->assertEquals(100, $color->getG());
        $this->assertEquals(50, $color->getB());
    }

    public function testCreateColorFromHexWithoutHash(): void
    {
        $color = Color::hex('3498db');
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGB::getName(), $color->getColorSpaceName());
        $this->assertEquals(52, $color->getR());
        $this->assertEquals(152, $color->getG());
        $this->assertEquals(219, $color->getB());
    }

    public function testCreateColorFromHex8Digit(): void
    {
        $color = Color::hex('#FF643280', RGBA::class);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGBA::getName(), $color->getColorSpaceName());
        $this->assertEquals(255, $color->getR());
        $this->assertEquals(100, $color->getG());
        $this->assertEquals(50, $color->getB());
        $this->assertEquals(128, $color->getA());
    }

    public function testCreateColorFromHex3Digit(): void
    {
        $color = Color::hex('#F64');
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGB::getName(), $color->getColorSpaceName());
        $this->assertEquals(255, $color->getR());
        $this->assertEquals(102, $color->getG());
        $this->assertEquals(68, $color->getB());
    }

    // ========== Color Conversion Tests ==========

    public function testConvertRgbToRgb(): void
    {
        $original = Color::rgb(255, 100, 50);
        $converted = $original->toRGB();
        
        $this->assertEquals(RGB::getName(), $converted->getColorSpaceName());
        $this->assertEquals(255, $converted->getR());
        $this->assertEquals(100, $converted->getG());
        $this->assertEquals(50, $converted->getB());
    }

    public function testConvertRgbToCmyk(): void
    {
        $rgb = Color::rgb(255, 100, 50);
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
        $cmyk = Color::cmyk(0, 61, 80, 0);
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
        $rgb = Color::rgb(255, 100, 50);
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
        $hsl = Color::hsl(210, 50, 40);
        $rgb = $hsl->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        $this->assertEquals(51, $rgb->getR());
        $this->assertEquals(102, $rgb->getG());
        $this->assertEquals(153, $rgb->getB());
    }

    public function testConvertRgbToHsv(): void
    {
        $rgb = Color::rgb(255, 100, 50);
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
        $hsv = Color::hsv(210, 50, 60);
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
        $rgb = Color::rgb(255, 100, 50);
        $lab = $rgb->toLab();
        
        $this->assertEquals(Lab::getName(), $lab->getColorSpaceName());
        // Lab values are rounded to int: L: 0-100, a: -128 to 127, b: -128 to 127
        $this->assertIsInt($lab->getL());
        $this->assertIsInt($lab->getA());
        $this->assertIsInt($lab->getB());
    }

    public function testConvertLabToRgb(): void
    {
        $lab = Color::lab(75, 20, -30);
        $rgb = $lab->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $rgb->getR());
        $this->assertLessThanOrEqual(255, $rgb->getR());
        $this->assertGreaterThanOrEqual(0, $rgb->getG());
        $this->assertLessThanOrEqual(255, $rgb->getG());
        $this->assertGreaterThanOrEqual(0, $rgb->getB());
        $this->assertLessThanOrEqual(255, $rgb->getB());
    }

    public function testConvertRgbToLch(): void
    {
        $rgb = Color::rgb(255, 100, 50);
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
        $lch = Color::lch(75, 36, 210);
        $rgb = $lch->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $rgb->getR());
        $this->assertLessThanOrEqual(255, $rgb->getR());
        $this->assertGreaterThanOrEqual(0, $rgb->getG());
        $this->assertLessThanOrEqual(255, $rgb->getG());
        $this->assertGreaterThanOrEqual(0, $rgb->getB());
        $this->assertLessThanOrEqual(255, $rgb->getB());
    }

    public function testConvertRgbToXyz(): void
    {
        $rgb = Color::rgb(255, 100, 50);
        $xyz = $rgb->toXYZ();
        
        $this->assertEquals(XYZ::getName(), $xyz->getColorSpaceName());
        // XYZ values are rounded to int
        $this->assertIsInt($xyz->getX());
        $this->assertIsInt($xyz->getY());
        $this->assertIsInt($xyz->getZ());
    }

    public function testConvertXyzToRgb(): void
    {
        $xyz = Color::xyz(25, 30, 35);
        $rgb = $xyz->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        $this->assertGreaterThanOrEqual(0, $rgb->getR());
        $this->assertLessThanOrEqual(255, $rgb->getR());
        $this->assertGreaterThanOrEqual(0, $rgb->getG());
        $this->assertLessThanOrEqual(255, $rgb->getG());
        $this->assertGreaterThanOrEqual(0, $rgb->getB());
        $this->assertLessThanOrEqual(255, $rgb->getB());
    }

    public function testConvertRgbToYcbcr(): void
    {
        $rgb = Color::rgb(255, 100, 50);
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
        $ycbcr = Color::ycbcr(78, 0, -100);
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
        $original = Color::rgb(255, 100, 50);
        $cmyk = $original->toCMYK();
        $backToRgb = $cmyk->toRGB();
        
        // Allow for small rounding differences
        $this->assertEqualsWithDelta($original->getR(), $backToRgb->getR(), 1);
        $this->assertEqualsWithDelta($original->getG(), $backToRgb->getG(), 1);
        $this->assertEqualsWithDelta($original->getB(), $backToRgb->getB(), 1);
    }

    public function testRoundTripRgbToHslAndBack(): void
    {
        $original = Color::rgb(255, 100, 50);
        $hsl = $original->toHSL();
        $backToRgb = $hsl->toRGB();
        
        // Allow for small rounding differences (HSL conversions can have larger rounding errors)
        $this->assertEqualsWithDelta($original->getR(), $backToRgb->getR(), 2);
        $this->assertEqualsWithDelta($original->getG(), $backToRgb->getG(), 2);
        $this->assertEqualsWithDelta($original->getB(), $backToRgb->getB(), 2);
    }

    public function testRoundTripRgbToHsvAndBack(): void
    {
        $original = Color::rgb(255, 100, 50);
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
        $color = Color::rgb(255, 100, 50);
        $hex = $color->toHex();
        
        $this->assertEquals('#FF6432', $hex);
    }

    public function testRgbaToHex(): void
    {
        $color = Color::rgba(255, 100, 50, 128);
        $hex = $color->toHex();
        
        $this->assertEquals('#FF643280', $hex);
    }

    public function testColorSpaceConversionToHex(): void
    {
        $hsl = Color::hsl(210, 50, 40);
        $hex = $hsl->toHex();
        
        // Should convert to RGB first, then to hex
        $this->assertStringStartsWith('#', $hex);
        $this->assertEquals(7, strlen($hex)); // #RRGGBB format
    }

    // ========== Channel Access Tests ==========

    public function testGetChannelValues(): void
    {
        $color = Color::rgb(255, 100, 50);
        
        $this->assertEquals(255, $color->getChannel('r'));
        $this->assertEquals(100, $color->getChannel('g'));
        $this->assertEquals(50, $color->getChannel('b'));
        $this->assertEquals(255, $color->getR());
        $this->assertEquals(100, $color->getG());
        $this->assertEquals(50, $color->getB());
    }

    public function testGetChannels(): void
    {
        $color = Color::rgb(255, 100, 50);
        $channels = $color->getChannels();
        
        $this->assertIsArray($channels);
        $this->assertContains('r', $channels);
        $this->assertContains('g', $channels);
        $this->assertContains('b', $channels);
    }

    // ========== Immutability Tests ==========

    public function testWithMethodCreatesNewInstance(): void
    {
        $original = Color::rgb(255, 100, 50);
        $modified = $original->with(['r' => 200]);
        
        $this->assertNotSame($original, $modified);
        $this->assertEquals(255, $original->getR());
        $this->assertEquals(200, $modified->getR());
        $this->assertEquals(100, $modified->getG()); // Unchanged
        $this->assertEquals(50, $modified->getB()); // Unchanged
    }

    public function testWithoutMethodCreatesNewInstance(): void
    {
        $original = Color::rgb(255, 100, 50);
        $modified = $original->without(['r']);
        
        $this->assertNotSame($original, $modified);
        $this->assertEquals(255, $original->getR());
        $this->assertEquals(0, $modified->getR()); // Reset to default
    }
}

<?php

declare(strict_types=1);

namespace Negarity\Color\Tests;

use PHPUnit\Framework\TestCase;
use Negarity\Color\Color;
use Negarity\Color\Exception\InvalidColorValueException;
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
use Negarity\Color\Registry\ColorSpaceRegistry;

final class ColorCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ColorSpaceRegistry::registerBuiltIn();
    }

    // ========== Valid Color Creation Tests ==========

    public function testCreateRgbColor(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $color = Color::rgb($r, $g, $b);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGB::getName(), $color->getColorSpaceName());
        $this->assertEquals($r, $color->getR());
        $this->assertEquals($g, $color->getG());
        $this->assertEquals($b, $color->getB());
    }

    public function testCreateRgbaColor(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $a = random_int(0, 255);
        $color = Color::rgba($r, $g, $b, $a);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGBA::getName(), $color->getColorSpaceName());
        $this->assertEquals($r, $color->getR());
        $this->assertEquals($g, $color->getG());
        $this->assertEquals($b, $color->getB());
        $this->assertEquals($a, $color->getA());
    }

    public function testCreateCmykColor(): void
    {
        $c = random_int(0, 100);
        $m = random_int(0, 100);
        $y = random_int(0, 100);
        $k = random_int(0, 100);
        $color = Color::cmyk($c, $m, $y, $k);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(CMYK::getName(), $color->getColorSpaceName());
        $this->assertEquals($c, $color->getC());
        $this->assertEquals($m, $color->getM());
        $this->assertEquals($y, $color->getY());
        $this->assertEquals($k, $color->getK());
    }

    public function testCreateHslColor(): void
    {
        $h = random_int(0, 360);
        $s = random_int(0, 100);
        $l = random_int(0, 100);
        $color = Color::hsl($h, $s, $l);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(HSL::getName(), $color->getColorSpaceName());
        $this->assertEquals($h, $color->getH());
        $this->assertEquals($s, $color->getS());
        $this->assertEquals($l, $color->getL());
    }

    public function testCreateHslaColor(): void
    {
        $h = random_int(0, 360);
        $s = random_int(0, 100);
        $l = random_int(0, 100);
        $a = random_int(0, 255);
        $color = Color::hsla($h, $s, $l, $a);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(HSLA::getName(), $color->getColorSpaceName());
        $this->assertEquals($h, $color->getH());
        $this->assertEquals($s, $color->getS());
        $this->assertEquals($l, $color->getL());
        $this->assertEquals($a, $color->getA());
    }

    public function testCreateHsvColor(): void
    {
        $h = random_int(0, 360);
        $s = random_int(0, 100);
        $v = random_int(0, 100);
        $color = Color::hsv($h, $s, $v);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(HSV::getName(), $color->getColorSpaceName());
        $this->assertEquals($h, $color->getH());
        $this->assertEquals($s, $color->getS());
        $this->assertEquals($v, $color->getV());
    }

    public function testCreateLabColor(): void
    {
        $l = random_int(0, 100);
        $a = random_int(-128, 127);
        $b = random_int(-128, 127);
        $color = Color::lab($l, $a, $b);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(Lab::getName(), $color->getColorSpaceName());
        $this->assertEquals($l, $color->getL());
        $this->assertEquals($a, $color->getA());
        $this->assertEquals($b, $color->getB());
    }

    public function testCreateLchColor(): void
    {
        $l = random_int(0, 100);
        $c = random_int(0, 100);
        $h = random_int(0, 360);
        $color = Color::lch($l, $c, $h);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(LCh::getName(), $color->getColorSpaceName());
        $this->assertEquals($l, $color->getL());
        $this->assertEquals($c, $color->getC());
        $this->assertEquals($h, $color->getH());
    }

    public function testCreateXyzColor(): void
    {
        $x = random_int(0, 100);
        $y = random_int(0, 100);
        $z = random_int(0, 100);
        $color = Color::xyz($x, $y, $z);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(XYZ::getName(), $color->getColorSpaceName());
        $this->assertEquals($x, $color->getX());
        $this->assertEquals($y, $color->getY());
        $this->assertEquals($z, $color->getZ());
    }

    public function testCreateYcbcrColor(): void
    {
        $y = random_int(0, 100);
        $cb = random_int(-128, 127);
        $cr = random_int(-128, 127);
        $color = Color::ycbcr($y, $cb, $cr);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(YCbCr::getName(), $color->getColorSpaceName());
        $this->assertEquals($y, $color->getY());
        $this->assertEquals($cb, $color->getCb());
        $this->assertEquals($cr, $color->getCr());
    }

    public function testCreateColorFromHex6Digit(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $hex = sprintf('#%02X%02X%02X', $r, $g, $b);
        
        $color = Color::hex($hex);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGB::getName(), $color->getColorSpaceName());
        $this->assertEquals($r, $color->getR());
        $this->assertEquals($g, $color->getG());
        $this->assertEquals($b, $color->getB());
    }

    public function testCreateColorFromHexWithoutHash(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $hex = sprintf('%02X%02X%02X', $r, $g, $b);
        
        $color = Color::hex($hex);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGB::getName(), $color->getColorSpaceName());
        $this->assertEquals($r, $color->getR());
        $this->assertEquals($g, $color->getG());
        $this->assertEquals($b, $color->getB());
    }

    public function testCreateColorFromHex8Digit(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $a = random_int(0, 255);
        $hex = sprintf('#%02X%02X%02X%02X', $r, $g, $b, $a);
        
        $color = Color::hex($hex, RGBA::class);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGBA::getName(), $color->getColorSpaceName());
        $this->assertEquals($r, $color->getR());
        $this->assertEquals($g, $color->getG());
        $this->assertEquals($b, $color->getB());
        $this->assertEquals($a, $color->getA());
    }

    public function testCreateColorFromHex3Digit(): void
    {
        // Generate random hex values that will work with 3-digit format
        // Each digit is repeated (e.g., #F64 becomes #FF6644)
        $rHex = dechex(random_int(0, 15));
        $gHex = dechex(random_int(0, 15));
        $bHex = dechex(random_int(0, 15));
        $hex = sprintf('#%s%s%s', $rHex, $gHex, $bHex);
        
        $color = Color::hex($hex);
        
        $this->assertInstanceOf(Color::class, $color);
        $this->assertEquals(RGB::getName(), $color->getColorSpaceName());
        // Verify the values match the expanded hex (e.g., #F64 -> FF6644)
        $expectedR = hexdec($rHex . $rHex);
        $expectedG = hexdec($gHex . $gHex);
        $expectedB = hexdec($bHex . $bHex);
        $this->assertEquals($expectedR, $color->getR());
        $this->assertEquals($expectedG, $color->getG());
        $this->assertEquals($expectedB, $color->getB());
    }

    // ========== Invalid Color Creation Tests ==========

    public function testCreateRgbColorWithInvalidRChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $invalidR = random_int(256, 500);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        Color::rgb($invalidR, $g, $b); // R > 255
    }

    public function testCreateRgbColorWithInvalidGChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $r = random_int(0, 255);
        $invalidG = random_int(-100, -1);
        $b = random_int(0, 255);
        Color::rgb($r, $invalidG, $b); // G < 0
    }

    public function testCreateRgbColorWithInvalidBChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $invalidB = random_int(256, 500);
        Color::rgb($r, $g, $invalidB); // B > 255
    }

    public function testCreateRgbaColorWithInvalidAlpha(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $invalidAlpha = random_int(256, 500);
        Color::rgba($r, $g, $b, $invalidAlpha); // Alpha > 255
    }

    public function testCreateCmykColorWithInvalidCChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $invalidC = random_int(101, 200);
        $m = random_int(0, 100);
        $y = random_int(0, 100);
        $k = random_int(0, 100);
        Color::cmyk($invalidC, $m, $y, $k); // C > 100
    }

    public function testCreateCmykColorWithInvalidMChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $c = random_int(0, 100);
        $invalidM = random_int(-100, -1);
        $y = random_int(0, 100);
        $k = random_int(0, 100);
        Color::cmyk($c, $invalidM, $y, $k); // M < 0
    }

    public function testCreateHslColorWithInvalidHChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $invalidH = random_int(361, 500);
        $s = random_int(0, 100);
        $l = random_int(0, 100);
        Color::hsl($invalidH, $s, $l); // H > 360
    }

    public function testCreateHslColorWithInvalidSChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $h = random_int(0, 360);
        $invalidS = random_int(101, 200);
        $l = random_int(0, 100);
        Color::hsl($h, $invalidS, $l); // S > 100
    }

    public function testCreateHslColorWithInvalidLChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $h = random_int(0, 360);
        $s = random_int(0, 100);
        $invalidL = random_int(-100, -1);
        Color::hsl($h, $s, $invalidL); // L < 0
    }

    public function testCreateHsvColorWithInvalidVChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $h = random_int(0, 360);
        $s = random_int(0, 100);
        $invalidV = random_int(101, 200);
        Color::hsv($h, $s, $invalidV); // V > 100
    }

    public function testCreateLabColorWithInvalidLChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $invalidL = random_int(101, 200);
        $a = random_int(-128, 127);
        $b = random_int(-128, 127);
        Color::lab($invalidL, $a, $b); // L > 100
    }

    public function testCreateLabColorWithNegativeLChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $invalidL = random_int(-100, -1);
        $a = random_int(-128, 127);
        $b = random_int(-128, 127);
        Color::lab($invalidL, $a, $b); // L < 0
    }

    public function testCreateLchColorWithInvalidHChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $l = random_int(0, 100);
        $c = random_int(0, 100);
        $invalidH = random_int(361, 500);
        Color::lch($l, $c, $invalidH); // H > 360
    }

    public function testCreateYcbcrColorWithInvalidYChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $invalidY = random_int(101, 200);
        $cb = random_int(-128, 127);
        $cr = random_int(-128, 127);
        Color::ycbcr($invalidY, $cb, $cr); // Y > 100
    }

    public function testCreateYcbcrColorWithInvalidCbChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $y = random_int(0, 100);
        $invalidCb = random_int(128, 255);
        $cr = random_int(-128, 127);
        Color::ycbcr($y, $invalidCb, $cr); // Cb > 127
    }

    public function testCreateYcbcrColorWithInvalidCrChannel(): void
    {
        $this->expectException(InvalidColorValueException::class);
        $y = random_int(0, 100);
        $cb = random_int(-128, 127);
        $invalidCr = random_int(-255, -129);
        Color::ycbcr($y, $cb, $invalidCr); // Cr < -128
    }

    // ========== Channel Access Tests ==========

    public function testGetChannelValues(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $color = Color::rgb($r, $g, $b);
        
        $this->assertEquals($r, $color->getChannel('r'));
        $this->assertEquals($g, $color->getChannel('g'));
        $this->assertEquals($b, $color->getChannel('b'));
        $this->assertEquals($r, $color->getR());
        $this->assertEquals($g, $color->getG());
        $this->assertEquals($b, $color->getB());
    }

    public function testGetChannels(): void
    {
        $color = Color::rgb(random_int(0, 255), random_int(0, 255), random_int(0, 255));
        $channels = $color->getChannels();
        
        $this->assertIsArray($channels);
        $this->assertContains('r', $channels);
        $this->assertContains('g', $channels);
        $this->assertContains('b', $channels);
    }

    // ========== Immutability Tests ==========

    public function testWithMethodCreatesNewInstance(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $original = Color::rgb($r, $g, $b);
        $newR = random_int(0, 255);
        $modified = $original->with(['r' => $newR]);
        
        $this->assertNotSame($original, $modified);
        $this->assertEquals($r, $original->getR());
        $this->assertEquals($newR, $modified->getR());
        $this->assertEquals($g, $modified->getG()); // Unchanged
        $this->assertEquals($b, $modified->getB()); // Unchanged
    }

    public function testWithoutMethodCreatesNewInstance(): void
    {
        $r = random_int(0, 255);
        $g = random_int(0, 255);
        $b = random_int(0, 255);
        $original = Color::rgb($r, $g, $b);
        $modified = $original->without(['r']);
        
        $this->assertNotSame($original, $modified);
        $this->assertEquals($r, $original->getR());
        $this->assertEquals(0, $modified->getR()); // Reset to default
    }
}

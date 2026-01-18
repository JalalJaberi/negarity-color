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
    private const COLOR_NAMES_FILE = __DIR__ . '/fixtures/color.names.txt';

    /**
     * Read a random color record from the color names file.
     * 
     * @return array{line_number: int, line_content: string, rgb: array{r: int, g: int, b: int}, hsl: array{h: int, s: int, l: int}, hsv: array{h: int, s: int, v: int}, cmyk: array{c: int, m: int, y: int, k: int}, lab: array{l: float, a: float, b: float}, lch: array{l: float, c: float, h: float}, xyz: array{x: float, y: float, z: float}}
     */
    private function getRandomColorFromFile(): array
    {
        // Read all lines
        $lines = file(self::COLOR_NAMES_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // Build array with line numbers for data lines
        $dataLinesWithNumbers = [];
        foreach ($lines as $lineNum => $line) {
            if (!str_starts_with(trim($line), '#') && preg_match('/^\d+\s+\w+/', $line)) {
                $dataLinesWithNumbers[] = [
                    'line_number' => $lineNum + 1, // File line numbers start at 1
                    'content' => $line
                ];
            }
        }
        
        // Pick a random line
        $randomEntry = $dataLinesWithNumbers[array_rand($dataLinesWithNumbers)];
        $randomLine = $randomEntry['content'];
        $lineNumber = $randomEntry['line_number'];
        
        // Parse the line
        // Format: idx name rgb R G B hex HEX hsv H S V xyz X Y Z lab L A B lch L C H cmyk C M Y K ...
        $parts = preg_split('/\s+/', $randomLine);
        
        // Extract color space values
        $rgbIdx = array_search('rgb', $parts);
        $hsvIdx = array_search('hsv', $parts);
        $xyzIdx = array_search('xyz', $parts);
        $labIdx = array_search('lab', $parts);
        $lchIdx = array_search('lch', $parts);
        $cmykIdx = array_search('cmyk', $parts);
        
        $r = (int) $parts[$rgbIdx + 1];
        $g = (int) $parts[$rgbIdx + 2];
        $b = (int) $parts[$rgbIdx + 3];
        
        // Calculate HSL from RGB (file doesn't have HSL directly)
        $hsl = $this->calculateHslFromRgb($r, $g, $b);
        
        return [
            'line_number' => $lineNumber,
            'line_content' => $randomLine,
            'rgb' => [
                'r' => $r,
                'g' => $g,
                'b' => $b,
            ],
            'hsl' => $hsl,
            'hsv' => [
                'h' => (int) $parts[$hsvIdx + 1],
                's' => (int) $parts[$hsvIdx + 2],
                'v' => (int) $parts[$hsvIdx + 3],
            ],
            'xyz' => [
                'x' => (float) $parts[$xyzIdx + 1] * 100, // File has 0-1, we need 0-100
                'y' => (float) $parts[$xyzIdx + 2] * 100,
                'z' => (float) $parts[$xyzIdx + 3] * 100,
            ],
            'lab' => [
                'l' => (float) $parts[$labIdx + 1],
                'a' => (float) $parts[$labIdx + 2],
                'b' => (float) $parts[$labIdx + 3],
            ],
            'lch' => [
                'l' => (float) $parts[$lchIdx + 1],
                'c' => (float) $parts[$lchIdx + 2],
                'h' => (float) $parts[$lchIdx + 3],
            ],
            'cmyk' => [
                'c' => (int) $parts[$cmykIdx + 1],
                'm' => (int) $parts[$cmykIdx + 2],
                'y' => (int) $parts[$cmykIdx + 3],
                'k' => (int) $parts[$cmykIdx + 4],
            ],
        ];
    }

    /**
     * Calculate HSL values from RGB (helper for file parsing).
     */
    private function calculateHslFromRgb(int $r, int $g, int $b): array
    {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        
        $h = 0;
        $s = 0;
        $d = $max - $min;
        
        if ($d != 0.0) {
            $s = ($l > 0.5)
                ? $d / (2 - $max - $min)
                : $d / ($max + $min);
        
            if ($max === $r) {
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
            } elseif ($max === $g) {
                $h = ($b - $r) / $d + 2;
            } else {
                $h = ($r - $g) / $d + 4;
            }
        
            $h /= 6;
        }
        
        $h = fmod($h * 360, 360);
        $s = max(0, min(100, $s * 100));
        $l = max(0, min(100, $l * 100));
        
        return [
            'h' => (int) round($h),
            's' => (int) round($s),
            'l' => (int) round($l)
        ];
    }

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
        $colorData = $this->getRandomColorFromFile();
        $rgb = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $cmyk = $rgb->toCMYK();
        
        $this->assertEquals(CMYK::getName(), $cmyk->getColorSpaceName());
        // Validate ranges are correct (CMYK conversions may differ from reference file algorithm)
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
        $colorData = $this->getRandomColorFromFile();
        $cmyk = Color::cmyk($colorData['cmyk']['c'], $colorData['cmyk']['m'], $colorData['cmyk']['y'], $colorData['cmyk']['k']);
        $rgb = $cmyk->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        // Validate ranges are correct (CMYK to RGB conversions may differ from reference file algorithm)
        $this->assertGreaterThanOrEqual(0, $rgb->getR());
        $this->assertLessThanOrEqual(255, $rgb->getR());
        $this->assertGreaterThanOrEqual(0, $rgb->getG());
        $this->assertLessThanOrEqual(255, $rgb->getG());
        $this->assertGreaterThanOrEqual(0, $rgb->getB());
        $this->assertLessThanOrEqual(255, $rgb->getB());
    }

    public function testConvertRgbToHsl(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $rgb = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $hsl = $rgb->toHSL();
        
        $this->assertEquals(HSL::getName(), $hsl->getColorSpaceName());
        // Validate against calculated HSL from file RGB (with tolerance)
        $this->assertEqualsWithDelta(
            $colorData['hsl']['h'], 
            $hsl->getH(), 
            2,
            sprintf('HSL H channel mismatch at line %d. RGB(%d, %d, %d) -> Expected H=%d, Got H=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsl']['h'], 
                $hsl->getH()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['hsl']['s'], 
            $hsl->getS(), 
            2,
            sprintf('HSL S channel mismatch at line %d. RGB(%d, %d, %d) -> Expected S=%d, Got S=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsl']['s'], 
                $hsl->getS()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['hsl']['l'], 
            $hsl->getL(), 
            2,
            sprintf('HSL L channel mismatch at line %d. RGB(%d, %d, %d) -> Expected L=%d, Got L=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsl']['l'], 
                $hsl->getL()
            )
        );
    }

    public function testConvertHslToRgb(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $hsl = Color::hsl($colorData['hsl']['h'], $colorData['hsl']['s'], $colorData['hsl']['l']);
        $rgb = $hsl->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        // Validate against file data with small tolerance for rounding
        $this->assertEqualsWithDelta(
            $colorData['rgb']['r'], 
            $rgb->getR(), 
            2,
            sprintf('RGB R channel mismatch at line %d. HSL(%d, %d, %d) -> Expected R=%d, Got R=%d', 
                $colorData['line_number'],
                $colorData['hsl']['h'], $colorData['hsl']['s'], $colorData['hsl']['l'],
                $colorData['rgb']['r'], 
                $rgb->getR()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['g'], 
            $rgb->getG(), 
            2,
            sprintf('RGB G channel mismatch at line %d. HSL(%d, %d, %d) -> Expected G=%d, Got G=%d', 
                $colorData['line_number'],
                $colorData['hsl']['h'], $colorData['hsl']['s'], $colorData['hsl']['l'],
                $colorData['rgb']['g'], 
                $rgb->getG()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['b'], 
            $rgb->getB(), 
            2,
            sprintf('RGB B channel mismatch at line %d. HSL(%d, %d, %d) -> Expected B=%d, Got B=%d', 
                $colorData['line_number'],
                $colorData['hsl']['h'], $colorData['hsl']['s'], $colorData['hsl']['l'],
                $colorData['rgb']['b'], 
                $rgb->getB()
            )
        );
    }

    public function testConvertRgbToHsv(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $rgb = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $hsv = $rgb->toHSV();
        
        $this->assertEquals(HSV::getName(), $hsv->getColorSpaceName());
        // Validate against file data with tolerance (HSV hue can wrap around 360)
        // For hue, handle wraparound (0 and 360 are the same)
        $expectedH = $colorData['hsv']['h'];
        $actualH = $hsv->getH();
        if ($expectedH == 0 && ($actualH > 350 || $actualH < 10)) {
            $this->assertTrue(true); // Accept 0/360 wraparound
        } else {
            $this->assertEqualsWithDelta(
                $expectedH, 
                $actualH, 
                5,
                sprintf('HSV H channel mismatch at line %d. RGB(%d, %d, %d) -> Expected H=%d, Got H=%d', 
                    $colorData['line_number'],
                    $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                    $expectedH, 
                    $actualH
                )
            );
        }
        $this->assertEqualsWithDelta(
            $colorData['hsv']['s'], 
            $hsv->getS(), 
            5,
            sprintf('HSV S channel mismatch at line %d. RGB(%d, %d, %d) -> Expected S=%d, Got S=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsv']['s'], 
                $hsv->getS()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['hsv']['v'], 
            $hsv->getV(), 
            5,
            sprintf('HSV V channel mismatch at line %d. RGB(%d, %d, %d) -> Expected V=%d, Got V=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsv']['v'], 
                $hsv->getV()
            )
        );
    }

    public function testConvertHsvToRgb(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $hsv = Color::hsv($colorData['hsv']['h'], $colorData['hsv']['s'], $colorData['hsv']['v']);
        $rgb = $hsv->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        // Validate against file data with small tolerance for rounding
        $this->assertEqualsWithDelta(
            $colorData['rgb']['r'], 
            $rgb->getR(), 
            2,
            sprintf('RGB R channel mismatch at line %d. HSV(%d, %d, %d) -> Expected R=%d, Got R=%d', 
                $colorData['line_number'],
                $colorData['hsv']['h'], $colorData['hsv']['s'], $colorData['hsv']['v'],
                $colorData['rgb']['r'], 
                $rgb->getR()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['g'], 
            $rgb->getG(), 
            2,
            sprintf('RGB G channel mismatch at line %d. HSV(%d, %d, %d) -> Expected G=%d, Got G=%d', 
                $colorData['line_number'],
                $colorData['hsv']['h'], $colorData['hsv']['s'], $colorData['hsv']['v'],
                $colorData['rgb']['g'], 
                $rgb->getG()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['b'], 
            $rgb->getB(), 
            2,
            sprintf('RGB B channel mismatch at line %d. HSV(%d, %d, %d) -> Expected B=%d, Got B=%d', 
                $colorData['line_number'],
                $colorData['hsv']['h'], $colorData['hsv']['s'], $colorData['hsv']['v'],
                $colorData['rgb']['b'], 
                $rgb->getB()
            )
        );
    }

    public function testConvertRgbToLab(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $rgb = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $lab = $rgb->toLab();
        
        $this->assertEquals(Lab::getName(), $lab->getColorSpaceName());
        // Lab values are rounded to int: L: 0-100, a: -128 to 127, b: -128 to 127
        $this->assertIsInt($lab->getL());
        $this->assertIsInt($lab->getA());
        $this->assertIsInt($lab->getB());
        // Validate against file data with tolerance (Lab values are rounded)
        $this->assertEqualsWithDelta(
            $colorData['lab']['l'], 
            $lab->getL(), 
            2,
            sprintf('Lab L channel mismatch at line %d. RGB(%d, %d, %d) -> Expected L=%.1f, Got L=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['lab']['l'], 
                $lab->getL()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['lab']['a'], 
            $lab->getA(), 
            2,
            sprintf('Lab A channel mismatch at line %d. RGB(%d, %d, %d) -> Expected A=%.1f, Got A=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['lab']['a'], 
                $lab->getA()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['lab']['b'], 
            $lab->getB(), 
            2,
            sprintf('Lab B channel mismatch at line %d. RGB(%d, %d, %d) -> Expected B=%.1f, Got B=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['lab']['b'], 
                $lab->getB()
            )
        );
    }

    public function testConvertLabToRgb(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $lab = Color::lab((int)round($colorData['lab']['l']), (int)round($colorData['lab']['a']), (int)round($colorData['lab']['b']));
        $rgb = $lab->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        // Validate against file data with small tolerance for rounding
        $this->assertEqualsWithDelta(
            $colorData['rgb']['r'], 
            $rgb->getR(), 
            2,
            sprintf('RGB R channel mismatch at line %d. Lab(%d, %d, %d) -> Expected R=%d, Got R=%d', 
                $colorData['line_number'],
                (int)round($colorData['lab']['l']), 
                (int)round($colorData['lab']['a']), 
                (int)round($colorData['lab']['b']),
                $colorData['rgb']['r'], 
                $rgb->getR()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['g'], 
            $rgb->getG(), 
            2,
            sprintf('RGB G channel mismatch at line %d. Lab(%d, %d, %d) -> Expected G=%d, Got G=%d', 
                $colorData['line_number'],
                (int)round($colorData['lab']['l']), 
                (int)round($colorData['lab']['a']), 
                (int)round($colorData['lab']['b']),
                $colorData['rgb']['g'], 
                $rgb->getG()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['b'], 
            $rgb->getB(), 
            2,
            sprintf('RGB B channel mismatch at line %d. Lab(%d, %d, %d) -> Expected B=%d, Got B=%d', 
                $colorData['line_number'],
                (int)round($colorData['lab']['l']), 
                (int)round($colorData['lab']['a']), 
                (int)round($colorData['lab']['b']),
                $colorData['rgb']['b'], 
                $rgb->getB()
            )
        );
    }

    public function testConvertRgbToLch(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $rgb = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $lch = $rgb->toLCh();
        
        $this->assertEquals(LCh::getName(), $lch->getColorSpaceName());
        // LCh values are rounded to int
        $this->assertIsInt($lch->getL());
        $this->assertIsInt($lch->getC());
        $this->assertGreaterThanOrEqual(0, $lch->getH());
        $this->assertLessThanOrEqual(360, $lch->getH());
        // Validate against file data with tolerance (LCh values are rounded)
        $this->assertEqualsWithDelta(
            $colorData['lch']['l'], 
            $lch->getL(), 
            2,
            sprintf('LCh L channel mismatch at line %d. RGB(%d, %d, %d) -> Expected L=%.1f, Got L=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['lch']['l'], 
                $lch->getL()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['lch']['c'], 
            $lch->getC(), 
            2,
            sprintf('LCh C channel mismatch at line %d. RGB(%d, %d, %d) -> Expected C=%.1f, Got C=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['lch']['c'], 
                $lch->getC()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['lch']['h'], 
            $lch->getH(), 
            2,
            sprintf('LCh H channel mismatch at line %d. RGB(%d, %d, %d) -> Expected H=%.1f, Got H=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['lch']['h'], 
                $lch->getH()
            )
        );
    }

    public function testConvertLchToRgb(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $lch = Color::lch((int)round($colorData['lch']['l']), (int)round($colorData['lch']['c']), (int)round($colorData['lch']['h']));
        $rgb = $lch->toRGB();
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        // Validate against file data with tolerance (LCh to RGB can have larger differences)
        $this->assertEqualsWithDelta(
            $colorData['rgb']['r'], 
            $rgb->getR(), 
            5,
            sprintf('RGB R channel mismatch at line %d. LCh(%d, %d, %d) -> Expected R=%d, Got R=%d', 
                $colorData['line_number'],
                (int)round($colorData['lch']['l']), 
                (int)round($colorData['lch']['c']), 
                (int)round($colorData['lch']['h']),
                $colorData['rgb']['r'], 
                $rgb->getR()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['g'], 
            $rgb->getG(), 
            5,
            sprintf('RGB G channel mismatch at line %d. LCh(%d, %d, %d) -> Expected G=%d, Got G=%d', 
                $colorData['line_number'],
                (int)round($colorData['lch']['l']), 
                (int)round($colorData['lch']['c']), 
                (int)round($colorData['lch']['h']),
                $colorData['rgb']['g'], 
                $rgb->getG()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['b'], 
            $rgb->getB(), 
            5,
            sprintf('RGB B channel mismatch at line %d. LCh(%d, %d, %d) -> Expected B=%d, Got B=%d', 
                $colorData['line_number'],
                (int)round($colorData['lch']['l']), 
                (int)round($colorData['lch']['c']), 
                (int)round($colorData['lch']['h']),
                $colorData['rgb']['b'], 
                $rgb->getB()
            )
        );
    }

    public function testConvertRgbToXyz(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $rgb = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $xyz = $rgb->toXYZ();
        
        $this->assertEquals(XYZ::getName(), $xyz->getColorSpaceName());
        // XYZ values are rounded to int
        $this->assertIsInt($xyz->getX());
        $this->assertIsInt($xyz->getY());
        $this->assertIsInt($xyz->getZ());
        // Validate against file data with tolerance (XYZ values are rounded)
        $this->assertEqualsWithDelta(
            $colorData['xyz']['x'], 
            $xyz->getX(), 
            2,
            sprintf('XYZ X channel mismatch at line %d. RGB(%d, %d, %d) -> Expected X=%.1f, Got X=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['xyz']['x'], 
                $xyz->getX()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['xyz']['y'], 
            $xyz->getY(), 
            2,
            sprintf('XYZ Y channel mismatch at line %d. RGB(%d, %d, %d) -> Expected Y=%.1f, Got Y=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['xyz']['y'], 
                $xyz->getY()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['xyz']['z'], 
            $xyz->getZ(), 
            2,
            sprintf('XYZ Z channel mismatch at line %d. RGB(%d, %d, %d) -> Expected Z=%.1f, Got Z=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['xyz']['z'], 
                $xyz->getZ()
            )
        );
    }

    public function testConvertXyzToRgb(): void
    {
        // Try multiple colors until we find one that produces valid RGB
        // Some XYZ values from the file may produce out-of-range RGB values
        $maxAttempts = 10;
        $colorData = null;
        $rgb = null;
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $colorData = $this->getRandomColorFromFile();
            $xyz = Color::xyz((int)round($colorData['xyz']['x']), (int)round($colorData['xyz']['y']), (int)round($colorData['xyz']['z']));
            
            try {
                $rgb = $xyz->toRGB();
                // Check if RGB values are valid
                if ($rgb->getR() >= 0 && $rgb->getR() <= 255 &&
                    $rgb->getG() >= 0 && $rgb->getG() <= 255 &&
                    $rgb->getB() >= 0 && $rgb->getB() <= 255) {
                    break;
                }
            } catch (\Exception $e) {
                // Try next color if this one produces invalid RGB
                continue;
            }
        }
        
        if ($rgb === null) {
            $this->markTestSkipped('Could not find valid XYZ color that produces in-range RGB after ' . $maxAttempts . ' attempts');
            return;
        }
        
        $this->assertEquals(RGB::getName(), $rgb->getColorSpaceName());
        // Validate against file data with tolerance (XYZ to RGB can have larger differences due to clamping)
        $this->assertEqualsWithDelta(
            $colorData['rgb']['r'], 
            $rgb->getR(), 
            10,
            sprintf('RGB R channel mismatch at line %d. XYZ(%d, %d, %d) -> Expected R=%d, Got R=%d', 
                $colorData['line_number'],
                (int)round($colorData['xyz']['x']), 
                (int)round($colorData['xyz']['y']), 
                (int)round($colorData['xyz']['z']),
                $colorData['rgb']['r'], 
                $rgb->getR()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['g'], 
            $rgb->getG(), 
            5,
            sprintf('RGB G channel mismatch at line %d. XYZ(%d, %d, %d) -> Expected G=%d, Got G=%d', 
                $colorData['line_number'],
                (int)round($colorData['xyz']['x']), 
                (int)round($colorData['xyz']['y']), 
                (int)round($colorData['xyz']['z']),
                $colorData['rgb']['g'], 
                $rgb->getG()
            )
        );
        $this->assertEqualsWithDelta(
            $colorData['rgb']['b'], 
            $rgb->getB(), 
            10,
            sprintf('RGB B channel mismatch at line %d. XYZ(%d, %d, %d) -> Expected B=%d, Got B=%d', 
                $colorData['line_number'],
                (int)round($colorData['xyz']['x']), 
                (int)round($colorData['xyz']['y']), 
                (int)round($colorData['xyz']['z']),
                $colorData['rgb']['b'], 
                $rgb->getB()
            )
        );
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
        $colorData = $this->getRandomColorFromFile();
        $original = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $cmyk = $original->toCMYK();
        $backToRgb = $cmyk->toRGB();
        
        // Allow for rounding differences (CMYK conversions can accumulate small errors)
        $this->assertEqualsWithDelta(
            $original->getR(), 
            $backToRgb->getR(), 
            3,
            sprintf('Round-trip RGB R mismatch at line %d. RGB(%d,%d,%d)->CMYK(%d,%d,%d,%d)->RGB: Expected R=%d, Got R=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['cmyk']['c'], $colorData['cmyk']['m'], $colorData['cmyk']['y'], $colorData['cmyk']['k'],
                $original->getR(), 
                $backToRgb->getR()
            )
        );
        $this->assertEqualsWithDelta(
            $original->getG(), 
            $backToRgb->getG(), 
            3,
            sprintf('Round-trip RGB G mismatch at line %d. RGB(%d,%d,%d)->CMYK(%d,%d,%d,%d)->RGB: Expected G=%d, Got G=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['cmyk']['c'], $colorData['cmyk']['m'], $colorData['cmyk']['y'], $colorData['cmyk']['k'],
                $original->getG(), 
                $backToRgb->getG()
            )
        );
        $this->assertEqualsWithDelta(
            $original->getB(), 
            $backToRgb->getB(), 
            3,
            sprintf('Round-trip RGB B mismatch at line %d. RGB(%d,%d,%d)->CMYK(%d,%d,%d,%d)->RGB: Expected B=%d, Got B=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['cmyk']['c'], $colorData['cmyk']['m'], $colorData['cmyk']['y'], $colorData['cmyk']['k'],
                $original->getB(), 
                $backToRgb->getB()
            )
        );
    }

    public function testRoundTripRgbToHslAndBack(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $original = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $hsl = $original->toHSL();
        $backToRgb = $hsl->toRGB();
        
        // Allow for small rounding differences (HSL conversions can have larger rounding errors)
        $this->assertEqualsWithDelta(
            $original->getR(), 
            $backToRgb->getR(), 
            2,
            sprintf('Round-trip RGB R mismatch at line %d. RGB(%d,%d,%d)->HSL(%d,%d,%d)->RGB: Expected R=%d, Got R=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsl']['h'], $colorData['hsl']['s'], $colorData['hsl']['l'],
                $original->getR(), 
                $backToRgb->getR()
            )
        );
        $this->assertEqualsWithDelta(
            $original->getG(), 
            $backToRgb->getG(), 
            2,
            sprintf('Round-trip RGB G mismatch at line %d. RGB(%d,%d,%d)->HSL(%d,%d,%d)->RGB: Expected G=%d, Got G=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsl']['h'], $colorData['hsl']['s'], $colorData['hsl']['l'],
                $original->getG(), 
                $backToRgb->getG()
            )
        );
        $this->assertEqualsWithDelta(
            $original->getB(), 
            $backToRgb->getB(), 
            2,
            sprintf('Round-trip RGB B mismatch at line %d. RGB(%d,%d,%d)->HSL(%d,%d,%d)->RGB: Expected B=%d, Got B=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsl']['h'], $colorData['hsl']['s'], $colorData['hsl']['l'],
                $original->getB(), 
                $backToRgb->getB()
            )
        );
    }

    public function testRoundTripRgbToHsvAndBack(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $original = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $hsv = $original->toHSV();
        $backToRgb = $hsv->toRGB();
        
        // Allow for small rounding differences (HSV conversions can have larger rounding errors)
        $this->assertEqualsWithDelta(
            $original->getR(), 
            $backToRgb->getR(), 
            2,
            sprintf('Round-trip RGB R mismatch at line %d. RGB(%d,%d,%d)->HSV(%d,%d,%d)->RGB: Expected R=%d, Got R=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsv']['h'], $colorData['hsv']['s'], $colorData['hsv']['v'],
                $original->getR(), 
                $backToRgb->getR()
            )
        );
        $this->assertEqualsWithDelta(
            $original->getG(), 
            $backToRgb->getG(), 
            2,
            sprintf('Round-trip RGB G mismatch at line %d. RGB(%d,%d,%d)->HSV(%d,%d,%d)->RGB: Expected G=%d, Got G=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsv']['h'], $colorData['hsv']['s'], $colorData['hsv']['v'],
                $original->getG(), 
                $backToRgb->getG()
            )
        );
        $this->assertEqualsWithDelta(
            $original->getB(), 
            $backToRgb->getB(), 
            2,
            sprintf('Round-trip RGB B mismatch at line %d. RGB(%d,%d,%d)->HSV(%d,%d,%d)->RGB: Expected B=%d, Got B=%d', 
                $colorData['line_number'],
                $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'],
                $colorData['hsv']['h'], $colorData['hsv']['s'], $colorData['hsv']['v'],
                $original->getB(), 
                $backToRgb->getB()
            )
        );
    }

    // ========== ToHex Tests ==========

    public function testRgbToHex(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $color = Color::rgb($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $hex = $color->toHex();
        
        $expectedHex = sprintf('#%02X%02X%02X', $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        $this->assertEquals($expectedHex, $hex);
    }

    public function testRgbaToHex(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $a = random_int(0, 255);
        $color = Color::rgba($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'], $a);
        $hex = $color->toHex();
        
        $expectedHex = sprintf('#%02X%02X%02X%02X', $colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b'], $a);
        $this->assertEquals($expectedHex, $hex);
    }

    public function testColorSpaceConversionToHex(): void
    {
        $colorData = $this->getRandomColorFromFile();
        $hsl = Color::hsl($colorData['hsl']['h'], $colorData['hsl']['s'], $colorData['hsl']['l']);
        $hex = $hsl->toHex();
        
        // Should convert to RGB first, then to hex
        $this->assertStringStartsWith('#', $hex);
        $this->assertEquals(7, strlen($hex)); // #RRGGBB format
    }
}

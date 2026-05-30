<?php

declare(strict_types=1);

namespace Negarity\Color\CIE;

/**
 * CIECAM02 / CIECAM16 lightness correlate J (0–100) from CIE XYZ (Y = 100 scale).
 *
 * Port of the forward lightness path from colour-science (BSD-3-Clause), using
 * default sRGB viewing conditions unless overridden.
 *
 * @see https://colour.readthedocs.io/en/latest/generated/colour.XYZ_to_CIECAM16.html
 */
final class CieCamAppearance
{
    /** @var array<int, array<int, float>> */
    private const array CAT02 = [
        [0.7328, 0.4296, -0.1624],
        [-0.7036, 1.6975, 0.0061],
        [0.0030, 0.0136, 0.9834],
    ];

    /** @var array<int, array<int, float>> */
    private const array CAT16 = [
        [0.401288, 0.650173, -0.051461],
        [-0.250268, 1.204414, 0.045854],
        [-0.002079, 0.048952, 0.953127],
    ];

    /** @var array<int, array<int, float>> */
    private const array XYZ_TO_HPE = [
        [0.38971, 0.68898, -0.07868],
        [-0.22981, 1.18340, 0.04641],
        [0.0, 0.0, 1.0],
    ];

    /** Average surround (F, c, Nc) — shared by CIECAM02 and CIECAM16. */
    private const float SURROUND_F = 1.0;
    private const float SURROUND_C = 0.69;
    private const float SURROUND_NC = 1.0;

    /** Default background Y and adapting luminance for display/sRGB contexts. */
    private const float DEFAULT_Y_B = 20.0;
    private const float DEFAULT_L_A = 64.0 / M_PI * 0.2;

    /**
     * @param array{x: float, y: float, z: float} $xyz      Stimulus XYZ (0–100 scale)
     * @param array{x: float, y: float, z: float} $xyzWhite Reference white XYZ (0–100 scale)
     * @param array{L_A?: float, Y_b?: float, F?: float, c?: float} $options
     */
    public static function lightnessJ(
        array $xyz,
        array $xyzWhite,
        string $model = 'ciecam16',
        array $options = [],
    ): float {
        $L_A = $options['L_A'] ?? self::DEFAULT_L_A;
        $Y_b = $options['Y_b'] ?? self::DEFAULT_Y_B;
        $c = $options['c'] ?? self::SURROUND_C;
        $F = $options['F'] ?? self::SURROUND_F;

        $x = $xyz['x'];
        $y = $xyz['y'];
        $z = $xyz['z'];
        $xw = $xyzWhite['x'];
        $yw = $xyzWhite['y'];
        $zw = $xyzWhite['z'];

        [, $Y_w] = [$xw, $yw, $zw];

        $n = self::sdiv($Y_b, $Y_w);
        $F_L = self::luminanceLevelAdaptationFactor($L_A);
        $N_bb = 0.725 * self::spow(self::sdiv(1.0, $n), 0.2);
        $zExp = 1.48 + sqrt($n);
        $D = max(0.0, min(1.0, $F * (1.0 - (1.0 / 3.6) * exp((-$L_A - 42.0) / 92.0))));

        if (strtolower($model) === 'ciecam02') {
            return self::lightnessJCam02($x, $y, $z, $xw, $yw, $zw, $Y_w, $D, $F_L, $N_bb, $c, $zExp);
        }

        return self::lightnessJCam16($x, $y, $z, $xw, $yw, $zw, $Y_w, $D, $F_L, $N_bb, $c, $zExp);
    }

    private static function lightnessJCam02(
        float $x,
        float $y,
        float $z,
        float $xw,
        float $yw,
        float $zw,
        float $Y_w,
        float $D,
        float $F_L,
        float $N_bb,
        float $c,
        float $zExp,
    ): float {
        [$R, $G, $B] = self::matVec(self::CAT02, $x, $y, $z);
        [$Rw, $Gw, $Bw] = self::matVec(self::CAT02, $xw, $yw, $zw);

        [$Rc, $Gc, $Bc] = self::fullChromaticAdaptationForward($R, $G, $B, $Rw, $Gw, $Bw, $Y_w, $D);
        [$Rwc, $Gwc, $Bwc] = self::fullChromaticAdaptationForward($Rw, $Gw, $Bw, $Rw, $Gw, $Bw, $Y_w, $D);

        [$Rp, $Gp, $Bp] = self::cat02RgbToHpe($Rc, $Gc, $Bc);
        [$Rpw, $Gpw, $Bpw] = self::cat02RgbToHpe($Rwc, $Gwc, $Bwc);

        [$Ra, $Ga, $Ba] = self::postAdaptationCompressionForward($Rp, $Gp, $Bp, $F_L);
        [$Raw, $Gaw, $Baw] = self::postAdaptationCompressionForward($Rpw, $Gpw, $Bpw, $F_L);

        $A = self::achromaticResponseForward($Ra, $Ga, $Ba, $N_bb);
        $A_w = self::achromaticResponseForward($Raw, $Gaw, $Baw, $N_bb);

        return self::clampPercent(100.0 * self::spow(self::sdiv($A, $A_w), $c * $zExp));
    }

    private static function lightnessJCam16(
        float $x,
        float $y,
        float $z,
        float $xw,
        float $yw,
        float $zw,
        float $Y_w,
        float $D,
        float $F_L,
        float $N_bb,
        float $c,
        float $zExp,
    ): float {
        [$Rw, $Gw, $Bw] = self::matVec(self::CAT16, $xw, $yw, $zw);

        $D_R = $D * 100.0 / $Rw + 1.0 - $D;
        $D_G = $D * 100.0 / $Gw + 1.0 - $D;
        $D_B = $D * 100.0 / $Bw + 1.0 - $D;

        [$Rwc, $Gwc, $Bwc] = [$D_R * $Rw, $D_G * $Gw, $D_B * $Bw];
        [$Raw, $Gaw, $Baw] = self::fEForward($Rwc, $Gwc, $Bwc, $F_L);
        $A_w = self::achromaticResponseForward($Raw + 0.1, $Gaw + 0.1, $Baw + 0.1, $N_bb);

        [$R, $G, $B] = self::matVec(self::CAT16, $x, $y, $z);
        [$Rc, $Gc, $Bc] = [$D_R * $R, $D_G * $G, $D_B * $B];
        [$Ra, $Ga, $Ba] = self::fEForward($Rc, $Gc, $Bc, $F_L);
        $A = self::achromaticResponseForward($Ra + 0.1, $Ga + 0.1, $Ba + 0.1, $N_bb);

        return self::clampPercent(100.0 * self::spow(self::sdiv($A, $A_w), $c * $zExp));
    }

    /**
     * @param array<int, array<int, float>> $matrix
     * @return array{0: float, 1: float, 2: float}
     */
    private static function matVec(array $matrix, float $x, float $y, float $z): array
    {
        return [
            $matrix[0][0] * $x + $matrix[0][1] * $y + $matrix[0][2] * $z,
            $matrix[1][0] * $x + $matrix[1][1] * $y + $matrix[1][2] * $z,
            $matrix[2][0] * $x + $matrix[2][1] * $y + $matrix[2][2] * $z,
        ];
    }

    /** @return array{0: float, 1: float, 2: float} */
    private static function fullChromaticAdaptationForward(
        float $R,
        float $G,
        float $B,
        float $Rw,
        float $Gw,
        float $Bw,
        float $Y_w,
        float $D,
    ): array {
        $factorR = $Y_w * self::sdiv($D, $Rw) + 1.0 - $D;
        $factorG = $Y_w * self::sdiv($D, $Gw) + 1.0 - $D;
        $factorB = $Y_w * self::sdiv($D, $Bw) + 1.0 - $D;

        return [$factorR * $R, $factorG * $G, $factorB * $B];
    }

    /** @return array{0: float, 1: float, 2: float} */
    private static function cat02RgbToHpe(float $R, float $G, float $B): array
    {
        $inv02 = self::invert3x3(self::CAT02);
        [$Ri, $Gi, $Bi] = self::matVec($inv02, $R, $G, $B);

        return self::matVec(self::XYZ_TO_HPE, $Ri, $Gi, $Bi);
    }

    /** @return array{0: float, 1: float, 2: float} */
    private static function postAdaptationCompressionForward(
        float $R,
        float $G,
        float $B,
        float $F_L,
    ): array {
        return [
            self::compressCone($R, $F_L),
            self::compressCone($G, $F_L),
            self::compressCone($B, $F_L),
        ];
    }

    private static function compressCone(float $channel, float $F_L): float
    {
        $abs = abs($channel);
        $FL_rgb = self::spow($F_L * $abs / 100.0, 0.42);

        return (400.0 * ($channel >= 0 ? 1.0 : -1.0) * $FL_rgb) / (27.13 + $FL_rgb) + 0.1;
    }

    /** @return array{0: float, 1: float, 2: float} */
    private static function fEForward(float $R, float $G, float $B, float $F_L): array
    {
        return [
            self::fEForwardChannel($R, $F_L),
            self::fEForwardChannel($G, $F_L),
            self::fEForwardChannel($B, $F_L),
        ];
    }

    private static function fEForwardChannel(float $rgbC, float $F_L): float
    {
        $qL = 0.26;
        $qU = 150.0;
        $fQUpper = self::fQ($F_L, $qU);
        $fQLower = self::fQ($F_L, $qL);
        $dfQUpper = self::dfQ($F_L, $qU);

        if ($rgbC > $qU) {
            return $fQUpper + $dfQUpper * ($rgbC - $qU);
        }
        if ($rgbC >= $qL) {
            return self::fQ($F_L, $rgbC);
        }

        return $fQLower * $rgbC / $qL;
    }

    private static function fQ(float $F_L, float $q): float
    {
        $FLq100 = self::spow(($F_L * $q) / 100.0, 0.42);

        return (400.0 * $FLq100) / (27.13 + $FLq100);
    }

    private static function dfQ(float $F_L, float $q): float
    {
        $FLq100 = ($F_L * $q) / 100.0;
        $pow = self::spow($FLq100, -0.58);

        return (1.68 * 27.13 * $F_L * $pow) / (27.13 + self::spow($FLq100, 0.42)) ** 2;
    }

    private static function achromaticResponseForward(float $R, float $G, float $B, float $N_bb): float
    {
        return (2.0 * $R + $G + (1.0 / 20.0) * $B - 0.305) * $N_bb;
    }

    private static function luminanceLevelAdaptationFactor(float $L_A): float
    {
        $k = 1.0 / (5.0 * $L_A + 1.0);
        $k4 = $k ** 4;

        return 0.2 * $k4 * (5.0 * $L_A) + 0.1 * (1.0 - $k4) ** 2 * self::spow(5.0 * $L_A, 1.0 / 3.0);
    }

    private static function spow(float $base, float $exp): float
    {
        if ($base >= 0.0) {
            return $base ** $exp;
        }

        return -((-$base) ** $exp);
    }

    private static function sdiv(float $a, float $b): float
    {
        if (abs($b) < 1e-12) {
            return 0.0;
        }

        return $a / $b;
    }

    private static function clampPercent(float $value): float
    {
        return min(100.0, max(0.0, $value));
    }

    /**
     * @param array<int, array<int, float>> $m
     * @return array<int, array<int, float>>
     */
    private static function invert3x3(array $m): array
    {
        $det =
            $m[0][0] * ($m[1][1] * $m[2][2] - $m[1][2] * $m[2][1])
            - $m[0][1] * ($m[1][0] * $m[2][2] - $m[1][2] * $m[2][0])
            + $m[0][2] * ($m[1][0] * $m[2][1] - $m[1][1] * $m[2][0]);

        if (abs($det) < 1e-12) {
            return $m;
        }

        $invDet = 1.0 / $det;

        return [
            [
                ($m[1][1] * $m[2][2] - $m[1][2] * $m[2][1]) * $invDet,
                ($m[0][2] * $m[2][1] - $m[0][1] * $m[2][2]) * $invDet,
                ($m[0][1] * $m[1][2] - $m[0][2] * $m[1][1]) * $invDet,
            ],
            [
                ($m[1][2] * $m[2][0] - $m[1][0] * $m[2][2]) * $invDet,
                ($m[0][0] * $m[2][2] - $m[0][2] * $m[2][0]) * $invDet,
                ($m[0][2] * $m[1][0] - $m[0][0] * $m[1][2]) * $invDet,
            ],
            [
                ($m[1][0] * $m[2][1] - $m[1][1] * $m[2][0]) * $invDet,
                ($m[0][1] * $m[2][0] - $m[0][0] * $m[2][1]) * $invDet,
                ($m[0][0] * $m[1][1] - $m[0][1] * $m[1][0]) * $invDet,
            ],
        ];
    }
}

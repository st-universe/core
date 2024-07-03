<?php

namespace Stu\Lib\ModuleScreen;

final class GradientColor implements GradientColorInterface
{
    public function calculateGradientColor(int $modificator, int $lowestValue, int $highestValue): string
    {
        $color1 = '#00ff00';
        $color2 = '#ffd500';
        $color3 = '#FF0000';

        $diff = $highestValue - $lowestValue;
        $percent = 100 / $diff * ($modificator - $lowestValue);

        // Konvertiere die Hex-Farbcodes in RGB-Werte
        $rgb1 = $this->hexToRgb($color1);
        $rgb2 = $this->hexToRgb($color2);
        $rgb3 = $this->hexToRgb($color3);

        // Verteile den Prozentwert zwischen den Farben
        if ($percent <= 50) {
            $gradientPercent = $percent * 2;
            $gradientRgb = $this->calculateGradientRgb($rgb1, $rgb2, $gradientPercent);
        } else {
            $gradientPercent = (($percent - 50) * 2);
            $gradientRgb = $this->calculateGradientRgb($rgb2, $rgb3, $gradientPercent);
        }

        // Konvertiere den RGB-Wert zurück in einen Hex-Farbcode
        $gradientColor = $this->rgbToHex($gradientRgb);

        return $gradientColor;
    }

    /**
     * @return array<int, int|float>
     */
    private function hexToRgb(string $color): array
    {
        $color = ltrim($color, '#');
        $length = strlen($color);
        $b = 0;
        $g = 0;
        $r = 0;
        if ($length == 3) {
            $r = hexdec(substr($color, 0, 1) . substr($color, 0, 1));
            $g = hexdec(substr($color, 1, 1) . substr($color, 1, 1));
            $b = hexdec(substr($color, 2, 1) . substr($color, 2, 1));
        } elseif ($length == 6) {
            $r = hexdec(substr($color, 0, 2));
            $g = hexdec(substr($color, 2, 2));
            $b = hexdec(substr($color, 4, 2));
        }

        return [$r, $g, $b];
    }

    /**
     * @param array<mixed> $rgb1
     * @param array<mixed> $rgb2
     *
     * @return array<int>
     */
    private function calculateGradientRgb(array $rgb1, array $rgb2, float $percent): array
    {
        $r = (int) ($rgb1[0] + ($rgb2[0] - $rgb1[0]) * $percent / 100);
        $g = (int) ($rgb1[1] + ($rgb2[1] - $rgb1[1]) * $percent / 100);
        $b = (int) ($rgb1[2] + ($rgb2[2] - $rgb1[2]) * $percent / 100);

        return [$r, $g, $b];
    }

    /**
     * @param array<mixed> $rgb
     */
    private function rgbToHex(array $rgb): string
    {
        $r = str_pad(dechex((int) $rgb[0]), 2, '0', STR_PAD_LEFT);
        $g = str_pad(dechex((int) $rgb[1]), 2, '0', STR_PAD_LEFT);
        $b = str_pad(dechex((int) $rgb[2]), 2, '0', STR_PAD_LEFT);

        return '#' . $r . $g . $b;
    }
}

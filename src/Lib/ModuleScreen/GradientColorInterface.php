<?php

namespace Stu\Lib\ModuleScreen;

interface GradientColorInterface
{
    public function calculateGradientColor(int $modificator, int $lowestValue, int $highestValue): string;

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public function calculateGradientColorRGB(int $modificator, int $lowestValue, int $highestValue): array;
}

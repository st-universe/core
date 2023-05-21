<?php

namespace Stu\Lib\ModuleScreen;

interface GradientColorInterface
{
    public function calculateGradientColor(int $modificator, int $lowestValue, int $highestValue): string;
}

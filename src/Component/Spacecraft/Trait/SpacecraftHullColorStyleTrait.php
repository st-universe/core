<?php

namespace Stu\Component\Spacecraft\Trait;

trait SpacecraftHullColorStyleTrait
{
    use SpacecraftTrait;

    public function getHullColorStyle(): string
    {
        return $this->getColorStyle(
            $this->getThis()->getCondition()->getHull(),
            $this->getThis()->getMaxHull()
        );
    }

    private function getColorStyle(int $actual, int $max): string
    {
        // full
        if ($actual === $max) {
            return '';
        }

        // less than 100% - green
        if ($actual / $max > 0.75) {
            return 'color: #19c100;';
        }

        // less than 75% - yellow
        if ($actual / $max > 0.50) {
            return 'color: #f4e932;';
        }

        // less than 50% - orange
        if ($actual / $max > 0.25) {
            return 'color: #f48b28;';
        }

        // less than 25% - red
        return 'color: #ff3c3c;';
    }
}

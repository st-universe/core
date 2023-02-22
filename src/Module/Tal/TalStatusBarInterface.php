<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

interface TalStatusBarInterface
{
    public function setColor(string $color): TalStatusBarInterface;

    public function setLabel(string $label): TalStatusBarInterface;

    public function setMaxValue(int $maxValue): TalStatusBarInterface;

    public function setValue(int $value): TalStatusBarInterface;

    public function setSizeModifier(float $modifier): TalStatusBarInterface;

    public function render(): string;
}

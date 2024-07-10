<?php

declare(strict_types=1);

namespace Stu\Module\Template;

interface StatusBarInterface
{
    public function setColor(string $color): StatusBarInterface;

    public function setLabel(string $label): StatusBarInterface;

    public function setMaxValue(int $maxValue): StatusBarInterface;

    public function setValue(int $value): StatusBarInterface;

    public function setSizeModifier(float $modifier): StatusBarInterface;

    public function render(): string;
}

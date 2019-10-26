<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

final class TalStatusBar
{

    private $color = '';

    private $label = '';

    private $maxValue = 100;

    private $value = 0;

    private $sizeModifier = 1;

    public function setColor(string $color): TalStatusBar
    {
        $this->color = $color;
        return $this;
    }

    public function setLabel(string $label): TalStatusBar
    {
        $this->label = $label;
        return $this;
    }

    public function setMaxValue(int $maxValue): TalStatusBar
    {
        $this->maxValue = $maxValue;
        return $this;
    }

    public function setValue(int $value): TalStatusBar
    {
        $this->value = $value;
        return $this;
    }

    public function setSizeModifier(int $modifier): TalStatusBar
    {
        $this->sizeModifier = $modifier;
        return $this;
    }

    public function render(): string
    {
        $pro = max(0, @round((100 / $this->maxValue) * min($this->value, $this->maxValue)));
        $bar = $this->getStatusBar(
            $this->color,
            ceil($pro / 2)
        );
        if ($pro < 100) {
            $bar .= $this->getStatusBar(StatusBarColorEnum::STATUSBAR_GREY, floor((100 - $pro) / 2));
        }
        return $bar;
    }

    private function getStatusBar(string $color, float $amount): string
    {
        return sprintf(
            '<img
                alt="%s"
                src="assets/bars/balken.png"
                style="background-color: #%s;height: 12px; width:%dpx;"
                title="%s: %d/%d"
             />',
            $this->label,
            $color,
            round($amount) * $this->sizeModifier,
            $this->label,
            $this->value,
            $this->maxValue
        );
    }
}

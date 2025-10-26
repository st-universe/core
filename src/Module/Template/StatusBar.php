<?php

declare(strict_types=1);

namespace Stu\Module\Template;

class StatusBar implements StatusBarInterface
{
    private StatusBarColorEnum $color = StatusBarColorEnum::EMPTY;

    private string $label = '';

    private int $maxValue = 100;

    private int $value = 0;

    private float $sizeModifier = 1;

    #[\Override]
    public function setColor(StatusBarColorEnum $color): StatusBarInterface
    {
        $this->color = $color;
        return $this;
    }

    #[\Override]
    public function setLabel(string $label): StatusBarInterface
    {
        $this->label = $label;
        return $this;
    }

    #[\Override]
    public function setMaxValue(int $maxValue): StatusBarInterface
    {
        $this->maxValue = $maxValue;
        return $this;
    }

    #[\Override]
    public function setValue(int $value): StatusBarInterface
    {
        $this->value = $value;
        return $this;
    }

    #[\Override]
    public function setSizeModifier(float $modifier): StatusBarInterface
    {
        $this->sizeModifier = $modifier;
        return $this;
    }

    public function __toString(): string
    {
        return $this->render();
    }

    #[\Override]
    public function render(): string
    {
        if ($this->maxValue === 0 && $this->value === 0) {
            return $this->getStatusBar(StatusBarColorEnum::GREY, 50);
        }

        $pro = $this->maxValue === 0
            ? 100
            : max(0, @round((100 / $this->maxValue) * min($this->value, $this->maxValue)));
        $bar = $this->getStatusBar(
            $this->color,
            ceil($pro / 2)
        );
        if ($pro < 100) {
            $bar .= $this->getStatusBar(StatusBarColorEnum::GREY, floor((100 - $pro) / 2));
        }
        return $bar;
    }

    private function getStatusBar(StatusBarColorEnum $color, float $amount): string
    {
        return sprintf(
            '<img
                alt="%s"
                src="/assets/bars/balken.png"
                style="background-color: #%s;height: 12px; width:%dpx;"
                title="%s: %d/%d"
             />',
            $this->label,
            $color->value,
            (int) (round($amount) * $this->sizeModifier),
            $this->label,
            $this->value,
            $this->maxValue
        );
    }
}

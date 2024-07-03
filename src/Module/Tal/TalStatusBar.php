<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use Override;
final class TalStatusBar implements TalStatusBarInterface
{
    private string $color = '';

    private string $label = '';

    private int $maxValue = 100;

    private int $value = 0;

    private float $sizeModifier = 1;

    #[Override]
    public function setColor(string $color): TalStatusBarInterface
    {
        $this->color = $color;
        return $this;
    }

    #[Override]
    public function setLabel(string $label): TalStatusBarInterface
    {
        $this->label = $label;
        return $this;
    }

    #[Override]
    public function setMaxValue(int $maxValue): TalStatusBarInterface
    {
        $this->maxValue = $maxValue;
        return $this;
    }

    #[Override]
    public function setValue(int $value): TalStatusBarInterface
    {
        $this->value = $value;
        return $this;
    }

    #[Override]
    public function setSizeModifier(float $modifier): TalStatusBarInterface
    {
        $this->sizeModifier = $modifier;
        return $this;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->render();
    }

    #[Override]
    public function render(): string
    {
        $pro = $this->maxValue === 0 ? 100 : max(0, @round((100 / $this->maxValue) * min($this->value, $this->maxValue)));
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
                src="/assets/bars/balken.png"
                style="background-color: #%s;height: 12px; width:%dpx;"
                title="%s: %d/%d"
             />',
            $this->label,
            $color,
            (int) (round($amount) * $this->sizeModifier),
            $this->label,
            $this->value,
            $this->maxValue
        );
    }
}

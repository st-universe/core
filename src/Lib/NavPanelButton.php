<?php

declare(strict_types=1);

namespace Stu\Lib;

final class NavPanelButton implements NavPanelButtonInterface
{
    private string $label;

    private bool $disabled;

    public function __construct(
        string $label,
        bool $disabled = false
    ) {
        $this->label = $label;
        $this->disabled = $disabled;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }
}

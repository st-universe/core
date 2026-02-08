<?php

declare(strict_types=1);

namespace Stu\Lib\Map\NavPanel;

final class NavPanelButton implements NavPanelButtonInterface
{
    public function __construct(private string $label, private bool $disabled = false) {}

    #[\Override]
    public function getLabel(): string
    {
        return $this->label;
    }

    #[\Override]
    public function isDisabled(): bool
    {
        return $this->disabled;
    }
}

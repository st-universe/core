<?php

declare(strict_types=1);

namespace Stu\Lib\Map\NavPanel;

interface NavPanelButtonInterface
{
    public function getLabel(): string;

    public function isDisabled(): bool;
}

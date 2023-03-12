<?php

namespace Stu\Lib;

interface NavPanelButtonInterface
{
    public function getLabel(): string;

    public function isDisabled(): bool;
}

<?php

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;

class ViewContext
{
    public function __construct(
        private ModuleEnum $moduleView,
        private String $viewIdentifier
    ) {}

    public function getModule(): ModuleEnum
    {
        return $this->moduleView;
    }

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}

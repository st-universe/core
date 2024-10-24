<?php

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleViewEnum;

class ViewContext
{
    public function __construct(
        private ModuleViewEnum $moduleView,
        private String $viewIdentifier
    ) {}

    public function getModule(): ModuleViewEnum
    {
        return $this->moduleView;
    }

    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }
}

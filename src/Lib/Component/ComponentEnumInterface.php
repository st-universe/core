<?php

namespace Stu\Lib\Component;

use Stu\Component\Game\ModuleEnum;

interface ComponentEnumInterface
{
    public function getModuleView(): ModuleEnum;

    public function getTemplate(): string;

    public function hasTemplateVariables(): bool;

    public function getValue(): string;

    public function getRefreshIntervalInSeconds(): ?int;
}

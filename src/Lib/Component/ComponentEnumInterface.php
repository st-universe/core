<?php

namespace Stu\Lib\Component;

use Stu\Component\Game\ModuleViewEnum;

interface ComponentEnumInterface
{
    public function getModuleView(): ModuleViewEnum;

    public function getTemplate(): string;

    public function hasTemplateVariables(): bool;

    public function getValue(): string;

    public function getRefreshIntervalInSeconds(): ?int;
}

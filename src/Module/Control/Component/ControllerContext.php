<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component;

use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

interface ControllerContext extends TemplateInterface
{
    public function getGame(): GameControllerInterface;

    public function getInfo(): InformationWrapper;

    public function getUser(): User;

    public function setView(ModuleEnum|string $view): void;

    public function setViewContext(ViewContextMetadataTypeEnum $type, mixed $value): void;

    public function getSessionString(): string;

    public function addExecuteJS(string $value, JavascriptExecutionTypeEnum $when = JavascriptExecutionTypeEnum::BEFORE_RENDER): void;

}

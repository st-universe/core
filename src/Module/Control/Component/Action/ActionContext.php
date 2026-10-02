<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component\Action;

use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\Component\ControllerContext;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Orm\Entity\User;

class ActionContext implements ActionControllerContext {

    public function __construct(
        private readonly ControllerContext $context
    ) {}

    #[\Override]
    public function getGame(): GameControllerInterface {
        return $this->context->getGame();
    }

    #[\Override]
    public function getInfo(): InformationWrapper {
        return $this->context->getInfo();
    }

    #[\Override]
    public function getUser(): User {
        return $this->context->getUser();
    }

    #[\Override]
    public function setView(ModuleEnum|string $view): void {
        $this->context->setView($view);
    }

    #[\Override]
    public function setViewContext(ViewContextMetadataTypeEnum $type, mixed $value): void {
        $this->context->setViewContext($type, $value);
    }

    #[\Override]
    public function setTemplateVar(string $key, mixed $variable): void
    {
        $this->context->setTemplateVar($key, $variable);
    }

    #[\Override]
    public function getSessionString(): string {
        return $this->context->getSessionString();
    }

    #[\Override]
    public function addExecuteJS(string $value, JavascriptExecutionTypeEnum $when = JavascriptExecutionTypeEnum::BEFORE_RENDER): void {
        $this->context->addExecuteJS($value, $when);
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component;

use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameData;
use Stu\Module\Control\JavascriptExecutionInterface;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\User;

final class Context implements ControllerContext {

    public function __construct(
        private readonly GameControllerInterface $game,
        private readonly GameData $gameData,
        private readonly TwigPageInterface $twigPage,
        private readonly JavascriptExecutionInterface $javascriptExecution,
        private readonly SessionStringFactoryInterface $sessionStringFactory
    ) {}

    #[\Override]
    public function getGame(): GameControllerInterface {
        return $this->game;
    }

    #[\Override]
    public function setView(ModuleEnum|string $view): void
    {
        if ($view instanceof ModuleEnum) {
            unset($this->gameData->viewContext[ViewContextMetadataTypeEnum::VIEW->value]);
            $this->setViewContext(ViewContextMetadataTypeEnum::MODULE_VIEW, $view);
        } else {
            $this->setViewContext(ViewContextMetadataTypeEnum::VIEW, $view);
        }
    }

    #[\Override]
    public function setViewContext(ViewContextMetadataTypeEnum $type, mixed $value): void
    {
        $this->gameData->viewContext[$type->value] = $value;
    }

    #[\Override]
    public function setTemplateVar(string $key, mixed $variable): void
    {
        $this->twigPage->setVar($key, $variable);
    }

    #[\Override]
    public function addExecuteJS(string $value, JavascriptExecutionTypeEnum $when = JavascriptExecutionTypeEnum::BEFORE_RENDER): void
    {
        $this->javascriptExecution->addExecuteJS($value, $when);
    }

    #[\Override]
    public function getInfo(): InformationWrapper
    {
        return $this->gameData->gameInformations;
    }

    #[\Override]
    public function getSessionString(): string
    {
        return $this->sessionStringFactory->createSessionString($this->getUser());
    }

    #[\Override]
    public function getUser(): User {
        return $this->game->getUser();
    }
}

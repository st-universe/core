<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component\View;

use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Module\Control\Component\ControllerContext;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameData;
use Stu\Module\Control\JavascriptExecutionInterface;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\User;

class ViewContext implements ViewControllerContext
{
    private String $viewIdentifier;

    public function __construct(
        private readonly ControllerContext $context,
        private readonly GameData $gameData,
        private readonly ModuleEnum $module,
        private readonly TwigPageInterface $twigPage,
        private readonly GameSetupInterface $gameSetup,
        private readonly JavascriptExecutionInterface $javascriptExecution,
        private readonly SessionStringFactoryInterface $sessionStringFactory
    ) {}

    #[\Override]
    public function getGame(): GameControllerInterface
    {
        return $this->context->getGame();
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
    public function getViewContextMetadata(ViewContextMetadataTypeEnum $type): mixed
    {
        if (!array_key_exists($type->value, $this->gameData->viewContext)) {
            return null;
        }

        return $this->gameData->viewContext[$type->value];
    }

    #[\Override]
    public function setViewContext(ViewContextMetadataTypeEnum $type, mixed $value): void
    {
        $this->gameData->viewContext[$type->value] = $value;
    }

    #[\Override]
    public function setViewTemplate(string $viewTemplate): void
    {
        $this->gameSetup->setTemplateAndComponents($viewTemplate, $this);
    }

    #[\Override]
    public function setTemplateFile(string $template): void
    {
        $this->twigPage->setTemplate($template);
    }

    #[\Override]
    public function setMacroInAjaxWindow(string $macro): void
    {
        $this->gameData->macro = $macro;

        $this->setTemplateFile('html/ajaxwindow.twig');
    }

    #[\Override]
    public function showMacro(string $macro): void
    {
        $this->gameData->macro = $macro;

        $this->setTemplateFile('html/ajaxempty.twig');
    }

    #[\Override]
    public function setTemplateVar(string $key, mixed $variable): void
    {
        $this->twigPage->setVar($key, $variable);
    }

    #[\Override]
    public function setNavigation(
        array $navigationItems
    ): ViewControllerContext {
        foreach ($navigationItems as $item) {
            $this->appendNavigationPart($item['url'], $item['title']);
        }

        return $this;
    }

    #[\Override]
    public function appendNavigationPart(
        string $url,
        string $title
    ): void {
        $this->gameData->siteNavigation[$url] = $title;
    }

    #[\Override]
    public function setPageTitle(string $title): void
    {
        $this->gameData->pagetitle = $title;
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
    public function getUser(): User
    {
        return $this->context->getUser();
    }

    #[\Override]
    public function getModule(): ModuleEnum
    {
        return $this->module;
    }

    #[\Override]
    public function getViewIdentifier(): string
    {
        return $this->viewIdentifier;
    }

    public function setViewIdentifier(string $viewIdentifier): void
    {
        $this->viewIdentifier = $viewIdentifier;
    }
}

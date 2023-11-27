<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\Overview;

use request;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Lib\Component\ComponentEnum;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'OVERVIEW';

    private ViewComponentLoaderInterface $viewComponentLoader;

    private ComponentLoaderInterface $componentLoader;

    public function __construct(
        ViewComponentLoaderInterface $viewComponentLoader,
        ComponentLoaderInterface $componentLoader
    ) {
        $this->viewComponentLoader = $viewComponentLoader;
        $this->componentLoader = $componentLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile(ModuleViewEnum::GAME->getTemplate());

        $view = null;
        if (request::has('view')) {
            $view = ModuleViewEnum::tryFrom(request::getStringFatal('view'));
        }

        if ($view === null) {
            $view = $game->getViewContext()['VIEW'] ?? $game->getUser()->getDefaultView();
        }
        $game->setPageTitle($view->getTitle());

        /** @var ModuleViewEnum $view */
        $this->viewComponentLoader->registerViewComponents($view, $game);
        $game->setTemplateVar('TEMPLATE', $view->getTemplate());

        $this->registerComponents();
    }

    private function registerComponents(): void
    {
        foreach (ComponentEnum::cases() as $component) {
            $this->componentLoader->registerComponent($component);

            if ($component->getRefreshIntervalInSeconds() !== null) {
                $this->componentLoader->addComponentUpdate($component);
            }
        }
    }
}

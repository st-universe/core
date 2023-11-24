<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\Overview;

use request;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Lib\ViewComponentLoaderInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'OVERVIEW';

    private ViewComponentLoaderInterface $viewComponentLoader;

    public function __construct(
        ViewComponentLoaderInterface $viewComponentLoader
    ) {
        $this->viewComponentLoader = $viewComponentLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(ModuleViewEnum::GAME->getTitle());
        $game->setTemplateFile(ModuleViewEnum::GAME->getTemplate());

        $view = null;
        if (request::has('view')) {
            $view = ModuleViewEnum::tryFrom(request::getStringFatal('view'));
        }

        if ($view === null) {
            $view = $game->getViewContext()['VIEW'] ?? $game->getUser()->getDefaultView();
        }

        /** @var ModuleViewEnum $view */
        $this->viewComponentLoader->registerViewComponents($view, $game);
        $game->setTemplateVar('VIEW_TEMPLATE', $view->getTemplate());
    }
}

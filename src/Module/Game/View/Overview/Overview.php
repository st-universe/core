<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\Overview;

use request;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'OVERVIEW';

    private ViewComponentLoaderInterface $viewComponentLoader;

    private GameSetupInterface $gameSetup;

    public function __construct(
        ViewComponentLoaderInterface $viewComponentLoader,
        GameSetupInterface $gameSetup
    ) {
        $this->viewComponentLoader = $viewComponentLoader;
        $this->gameSetup = $gameSetup;
    }

    public function handle(GameControllerInterface $game): void
    {
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
        $this->gameSetup->setTemplateAndComponents($view->getTemplate(), $game);
    }
}

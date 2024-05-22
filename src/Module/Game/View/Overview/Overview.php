<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\Overview;

use request;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'OVERVIEW';

    public function __construct(
        private ViewComponentLoaderInterface $viewComponentLoader,
    ) {
    }

    public function handle(GameControllerInterface $game): void
    {
        $view = null;
        if (request::has('view')) {
            $view = ModuleViewEnum::tryFrom(request::getStringFatal('view'));
        }

        if ($view === null) {
            $view = $game->getViewContext(ViewContextTypeEnum::VIEW) ?? $game->getUser()->getDefaultView();
        }

        /** @var ModuleViewEnum $view */
        $this->viewComponentLoader->registerViewComponents($view, $game);

        $game->setPageTitle($view->getTitle());
        $game->setViewTemplate($view->getTemplate());
    }
}

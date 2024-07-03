<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\Overview;

use Override;
use request;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class Overview implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'OVERVIEW';

    public function __construct(
        private ViewComponentLoaderInterface $viewComponentLoader,
    ) {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $moduleView = $this->getModuleView($game);

        $this->viewComponentLoader->registerViewComponents($moduleView, $game);

        $game->setPageTitle($moduleView->getTitle());
        $game->setViewTemplate($moduleView->getTemplate());
    }

    private function getModuleView(GameControllerInterface $game): ModuleViewEnum
    {
        $moduleView = null;
        if (request::has('view')) {
            $moduleView = ModuleViewEnum::tryFrom(request::getStringFatal('view'));
        }

        if ($moduleView !== null) {
            return $moduleView;
        }

        return $game->getViewContext(ViewContextTypeEnum::MODULE_VIEW) ?? $game->getUser()->getDefaultView();
    }
}

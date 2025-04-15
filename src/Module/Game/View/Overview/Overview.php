<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\Overview;

use Override;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContext;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class Overview implements ViewControllerInterface, ViewWithTutorialInterface
{
    public const string VIEW_IDENTIFIER = 'OVERVIEW';

    private ModuleEnum $moduleView;

    public function __construct(
        private ViewComponentLoaderInterface $viewComponentLoader,
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $moduleView = $this->getModuleView($game);
        $this->moduleView = $this->getModuleView($game);
        $this->viewComponentLoader->registerViewComponents($moduleView, $game);

        $game->setPageTitle($moduleView->getTitle());
        $game->setViewTemplate($moduleView->getTemplate());
    }

    private function getModuleView(GameControllerInterface $game): ModuleEnum
    {
        $moduleView = null;
        if (request::has('view')) {
            $moduleView = ModuleEnum::tryFrom(request::getStringFatal('view'));
        }

        if ($moduleView !== null) {
            return $moduleView;
        }

        return $game->getViewContext(ViewContextTypeEnum::MODULE_VIEW) ?? $game->getUser()->getDefaultView();
    }

    #[Override]
    public function getViewContext(): ViewContext
    {
        return new ViewContext($this->moduleView, self::VIEW_IDENTIFIER);
    }
}

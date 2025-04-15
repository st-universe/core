<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowInnerContent;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContext;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;
use Stu\Module\Game\View\Overview\Overview;

final class ShowInnerContent implements ViewControllerInterface, ViewWithTutorialInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_INNER_CONTENT';

    private ModuleEnum $moduleView;

    public function __construct(private ViewComponentLoaderInterface $viewComponentLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        /** @var ModuleEnum  */
        $view = $game->getViewContext(ViewContextTypeEnum::MODULE_VIEW);

        $this->moduleView = $view;

        $this->viewComponentLoader->registerViewComponents($view, $game);
        $game->setTemplateVar('VIEW_TEMPLATE', $view->getTemplate());

        $game->showMacro('html/view/breadcrumbAndView.twig');
    }

    #[Override]
    public function getViewContext(): ViewContext
    {
        return new ViewContext($this->moduleView, Overview::VIEW_IDENTIFIER);
    }
}

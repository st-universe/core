<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowInnerContent;

use Override;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class ShowInnerContent implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_INNER_CONTENT';

    public function __construct(private ViewComponentLoaderInterface $viewComponentLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        /** @var ModuleViewEnum  */
        $view = $game->getViewContext(ViewContextTypeEnum::MODULE_VIEW);

        $this->viewComponentLoader->registerViewComponents($view, $game);
        $game->setTemplateVar('VIEW_TEMPLATE', $view->getTemplate());

        $game->showMacro('html/view/breadcrumbAndView.twig');
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowInnerContent;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class ShowInnerContent implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_INNER_CONTENT';

    private ViewComponentLoaderInterface $viewComponentLoader;

    public function __construct(
        ViewComponentLoaderInterface $viewComponentLoader
    ) {
        $this->viewComponentLoader = $viewComponentLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        /** @var ModuleViewEnum  */
        $view = $game->getViewContext()['VIEW'];

        $this->viewComponentLoader->registerViewComponents($view, $game);
        $game->setTemplateVar('VIEW_TEMPLATE', $view->getTemplate());

        $game->showMacro('html/view/breadcrumbAndView.twig');
    }
}

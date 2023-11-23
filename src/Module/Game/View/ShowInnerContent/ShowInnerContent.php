<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowInnerContent;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowInnerContent implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_INNER_CONTENT';

    public function handle(GameControllerInterface $game): void
    {
        $view = $game->getViewContext()['VIEW'];

        $game->setTemplateVar('VIEW_TEMPLATE', $view->getTemplate());

        $game->showMacro('html/view/breadcrumbAndView.twig');
    }
}

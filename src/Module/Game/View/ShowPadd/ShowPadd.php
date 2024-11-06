<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowPadd;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowPadd implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PADD';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/tutorial/padd.twig');
    }
}

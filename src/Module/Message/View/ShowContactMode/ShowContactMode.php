<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowContactMode;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowContactMode implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CONTACT_MODE';

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/user/contactMode.twig');
    }
}

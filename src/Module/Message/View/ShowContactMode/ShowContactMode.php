<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowContactMode;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowContactMode implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CONTACT_MODE';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/user/contactMode.twig');
    }
}

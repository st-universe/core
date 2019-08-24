<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowFinishRegistration;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowFinishRegistration implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_REGISTRATION_END';

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Registrierung abgeschlossen - Star Trek Universe'));
        $game->setTemplateFile('html/registration_end.xhtml');
    }
}

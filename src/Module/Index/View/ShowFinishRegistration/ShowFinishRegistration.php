<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowFinishRegistration;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowFinishRegistration implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_REGISTRATION_END';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Registrierung abgeschlossen - Star Trek Universe'));
        $game->setTemplateFile('html/registration_end.xhtml');
    }
}

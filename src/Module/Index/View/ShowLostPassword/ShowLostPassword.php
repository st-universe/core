<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowLostPassword;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowLostPassword implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LOST_PASSWORD';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/index_lostpassword.xhtml');
        $game->setPageTitle(_('Passwort vergessen - Star Trek Universe'));
    }
}

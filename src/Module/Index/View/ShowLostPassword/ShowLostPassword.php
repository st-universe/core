<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowLostPassword;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;

final class ShowLostPassword implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_LOST_PASSWORD';

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->setTemplateFile('html/index/lostPassword.twig');
        $game->setPageTitle(_('Passwort vergessen - Star Trek Universe'));
    }
}

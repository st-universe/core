<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowImprint;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowImprint implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_INFOS';

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Impressum - Star Trek Universe'));
        $game->setTemplateFile('html/index_impressum.xhtml');
    }
}

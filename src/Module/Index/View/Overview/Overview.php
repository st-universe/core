<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\Overview;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use SystemNews;

final class Overview implements ViewControllerInterface
{

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Star Trek Universe'));
        $game->setTemplateFile('html/index.xhtml');

        $game->setTemplateVar('SYSTEM_NEWS', SystemNews::getListBy('ORDER BY id ASC LIMIT 5'));
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\View\Overview;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{

    public function __construct(
    ) {
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );
        $game->setPageTitle(_('/ Optionen'));
        $game->setTemplateFile('html/options.xhtml');

        $game->setTemplateVar('USER', $game->getUser());
    }
}

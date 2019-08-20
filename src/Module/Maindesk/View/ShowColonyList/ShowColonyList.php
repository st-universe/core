<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\ShowColonyList;

use AccessViolation;
use Colony;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Tuple;

final class ShowColonyList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONYLIST';

    public function __construct()
    {
    }

    public function handle(GameControllerInterface $game): void
    {
        if ((int)$game->getUser() !== 1) {
            throw new AccessViolation();
        }
        $game->setTemplateFile("html/maindesk_colonylist.xhtml");
        $game->setPageTitle("Kolonie gründen");
        $game->addNavigationPart(new Tuple("?cb=getColonyList", _('Kolonie gründen')));

        $game->setTemplateVar(
            'FREE_PLANET_LIST',
            Colony::getFreeColonyList($game->getUser()->getFaction())
        );
    }
}

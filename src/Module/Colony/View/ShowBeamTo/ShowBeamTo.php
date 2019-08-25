<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBeamTo;

use AccessViolation;
use request;
use Ship;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBeamTo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BEAMTO';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = new Ship(request::getIntFatal('target'));

        if (!checkPosition($colony,$target) || ($target->cloakIsActive() && !$target->ownedByCurrentUser())) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Zu Schiff beamen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/show_ship_beamto');
        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('COLONY', $colony);
    }
}

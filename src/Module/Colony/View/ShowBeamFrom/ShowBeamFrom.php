<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBeamFrom;

use AccessViolation;
use request;
use Ship;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBeamFrom implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BEAMFROM';

    private $colonyLoader;

    private $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
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

        $game->setPageTitle(_('Von Schiff beamen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/show_ship_beamfrom');
        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('COLONY', $colony);
    }
}

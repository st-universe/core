<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowBeamToColony;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowBeamToColony implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY_BEAMTO';

    private $shipLoader;

    private $colonyRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = $this->colonyRepository->find((int)request::getIntFatal('target'));
        if ($target === null || $ship->canInteractWith($target, true) === false) {
            // @todo ships cant interact
            return;
        }

        $game->setPageTitle(_('Zu Kolonie beamen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/show_ship_beamto_colony');

        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('SHIP', $ship);
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowBeamTo;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowBeamTo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BEAMTO';

    private $shipLoader;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $user->getId()
        );

        $game->setPageTitle(_('Zu Schiff beamen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/entity_not_available');

        $target = $this->shipRepository->find(request::getIntFatal('target'));
        if ($target === null) {
            return;
        }
        if ($ship->canInteractWith($target) === false) {
            return;
        }

        $game->setMacro('html/shipmacros.xhtml/show_ship_beamto');

        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('OWNS_TARGET', $ship->getUser() === $user);
    }
}

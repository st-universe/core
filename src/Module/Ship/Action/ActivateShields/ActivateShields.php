<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateShields;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;
use SystemActivationWrapper;

final class ActivateShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_SHIELDS';

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
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getCloakState()) {
            $game->addInformation("Die Tarnung ist aktiviert");
            return;
        }
        if ($ship->isTraktorbeamActive()) {
            $game->addInformation(_("Der Traktorstrahl ist aktiviert"));
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        if ($ship->getShield() <= 1) {
            $game->addInformation("Schilde sind nicht aufgeladen");
            return;
        }
        $ship->cancelRepair();
        if ($ship->getDock()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDock(0);
        }
        $ship->setShieldState(true);

        $this->shipRepository->save($ship);

        $game->addInformation("Schilde aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

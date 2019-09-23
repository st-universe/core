<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SetRedAlert;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use SystemActivationWrapper;

final class SetRedAlert implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SET_RED_ALERT';

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

        $ship->setAlertState(3);

        $this->activatePhaser($ship, $game);
        $this->activateTorpedo($ship, $game);
        $this->activateShields($ship, $game);

        $game->addInformation("Die Alarmstufe wurde auf Rot geändert");

        $this->shipRepository->save($ship);
    }

    private function activateShields(ShipInterface $ship, GameControllerInterface $game): void {
        if ($ship->getShieldState()) {
            return;
        }
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

    private function activatePhaser(ShipInterface $ship, GameControllerInterface $game): void {
        if (!$ship->hasPhaser() || $ship->getPhaser()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        $ship->setPhaser(true);

        $this->shipRepository->save($ship);

        $game->addInformation("Strahlenwaffe aktiviert");
    }

    private function activateTorpedo(ShipInterface $ship, GameControllerInterface $game): void {
        if (!$ship->hasTorpedo() || $ship->getTorpedos()) {
            return;
        }
        if ($ship->getTorpedoCount() == 0) {
            $game->addInformation("Das Schiff hat keine Torpedos geladen");
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        $ship->setTorpedos(true);

        $this->shipRepository->save($ship);

        $game->addInformation("Torpedobänke aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

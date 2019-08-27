<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SetRedAlert;

use request;
use ShipData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class SetRedAlert implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SET_RED_ALERT';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
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
        $ship->save();
    }

    private function activateShields(ShipData $ship, GameControllerInterface $game): void {
        if ($ship->getShieldState()) {
            return;
        }
        if ($ship->cloakIsActive()) {
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
        if ($ship->isDocked()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDock(0);
        }
        $ship->setShieldState(1);
        $ship->save();
        $game->addInformation("Schilde aktiviert");
    }

    private function activatePhaser(ShipData $ship, GameControllerInterface $game): void {
        if (!$ship->hasPhaser() || $ship->phaserIsActive()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        $ship->setPhaser(1);
        $ship->save();
        $game->addInformation("Strahlenwaffe aktiviert");
    }

    private function activateTorpedo(ShipData $ship, GameControllerInterface $game): void {
        if (!$ship->hasTorpedo() || $ship->torpedoIsActive()) {
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
        $ship->setTorpedos(1);
        $ship->save();
        $game->addInformation("Torpedobänke aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

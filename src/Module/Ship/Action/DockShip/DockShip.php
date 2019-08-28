<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DockShip;

use DockingRights;
use PM;
use request;
use ShipData;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DockShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DOCK';

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
        $target = $this->shipLoader->getById(request::indInt('target'));
        if (!checkPosition($target, $ship)) {
            return;
        }

        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrew() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if (!$target->isBase()) {
            return;
        }
        if (!DockingRights::checkPrivilegeFor($target->getId(), $game->getUser())) {
            $game->addInformation('Das Andocken wurden verweigert');
            return;
        }
        if ($ship->isFleetLeader()) {
            $this->fleetDock($ship, $target, $game);
            return;
        }
        if ($ship->isDocked()) {
            return;
        }
        if ($ship->getEps() < SYSTEM_ECOST_DOCK) {
            $game->addInformation('Zum Andocken wird 1 Energie benötigt');
            return;
        }
        if (!$target->hasFreeDockingSlots()) {
            $game->addInformation('Zur Zeit sind alle Dockplätze belegt');
            return;
        }
        if ($ship->shieldIsActive()) {
            $game->addInformation("Die Schilde wurden deaktiviert");
            $ship->setShieldState(0);
        }
        if ($ship->cloakIsActive()) {
            $game->addInformation("Das Schiff ist getarnt");
            return;
        }
        $ship->cancelRepair();
        $ship->lowerEps(1);
        $ship->setDock($target->getId());
        $ship->save();

        PM::sendPm($userId, $target->getUserId(),
            'Die ' . $ship->getName() . ' hat an der ' . $target->getName() . ' angedockt', PM_SPECIAL_SHIP);
        $game->addInformation('Andockvorgang abgeschlossen');
    }

    private function fleetDock(ShipData $ship, ShipData $target, GameControllerInterface $game): void
    {
        $msg = array();
        $msg[] = _("Flottenbefehl ausgeführt: Andocken an ") . $target->getName();;
        $freeSlots = $target->getFreeDockingSlotCount();
        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if ($freeSlots <= 0) {
                $msg[] = _("Es sind alle Dockplätze belegt");
                break;
            }
            if ($ship->isDocked()) {
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_DOCK) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            if ($ship->cloakIsActive()) {
                $msg[] = $ship->getName() . _(': Das Schiff ist getarnt');
                continue;
            }
            $ship->cancelRepair();
            if ($ship->shieldIsActive()) {
                $msg[] = $ship->getName() . _(': Schilde deaktiviert');
                $ship->setShieldState(0);
            }
            $ship->setDock($target->getId());
            $ship->lowerEps(SYSTEM_ECOST_DOCK);
            $ship->save();
            $freeSlots--;
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

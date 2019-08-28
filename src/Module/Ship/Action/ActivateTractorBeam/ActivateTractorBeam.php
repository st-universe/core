<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateTractorBeam;

use PM;
use request;
use ShipSingleAttackCycle;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class ActivateTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_TRAKTOR';

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

        if ($ship->isTraktorBeamActive()) {
            return;
        }
        if ($ship->shieldIsActive()) {
            $game->addInformation("Die Schilde sind aktiviert");
            return;
        }
        if ($ship->isDocked()) {
            $game->addInformation("Das Schiff ist angedockt");
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 2);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        $ship = $this->shipLoader->getById(request::getIntFatal('target'));
        if ($ship->getRump()->isTrumfield()) {
            $game->addInformation("Das Trümmerfeld kann nicht erfasst werden");
            return;
        }
        if (!checkPosition($ship, $ship)) {
            return;
        }
        if ($ship->isBase()) {
            $game->addInformation("Die " . $ship->getName() . " kann nicht erfasst werden");
            return;
        }
        if ($ship->traktorbeamToShip()) {
            $game->addInformation("Das Schiff wird bereits vom Traktorstrahl der " . $ship->getTraktorShip()->getName() . " gehalten");
            return;
        }
        if ($ship->isInFleet() && $ship->getFleetId() == $ship->getFleetId()) {
            $game->addInformation("Die " . $ship->getName() . " befindet sich in der selben Flotte wie die " . $ship->getName());
            return;
        }
        if (($ship->getAlertState() == ALERT_YELLOW || $ship->getAlertState() == ALERT_RED) && !$ship->getUser()->isFriend($userId)) {
            if ($ship->isInFleet()) {
                $attacker = $ship->getFleet()->getShips();
            } else {
                $attacker = &$ship;
            }
            $obj = new ShipSingleAttackCycle($attacker, $ship, $ship->getFleetId(),$ship->getFleetId());
            $game->addInformationMergeDown($obj->getMessages());
            PM::sendPM($userId, $ship->getUserId(),
                "Die " . $ship->getName() . " versucht die " . $ship->getName() . " in Sektor " . $ship->getSectorString() . " mit dem Traktorstrahl zu erfassen. Folgende Aktionen wurden ausgeführt:\n" . infoToString($obj->getMessages()),
                PM_SPECIAL_SHIP);
        }
        if ($ship->shieldIsActive()) {
            $game->addInformation("Die " . $ship->getName() . " kann aufgrund der aktiven Schilde nicht erfasst werden");
            return;
        }
        $ship->deactivateTraktorBeam();
        $ship->setTraktorMode(1);
        $ship->setTraktorShipId($ship->getId());
        $ship->setTraktorMode(2);
        $ship->setTraktorShipId($ship->getId());
        $ship->save();
        $ship->save();
        if ($userId != $ship->getUserId()) {
            PM::sendPM($userId, $ship->getUserId(),
                "Die " . $ship->getName() . " wurde in SeKtor " . $ship->getSectorString() . " vom Traktorstrahl der " . $ship->getName() . " erfasst",
                PM_SPECIAL_SHIP);
        }
        $game->addInformation("Der Traktorstrahl wurde auf die " . $ship->getName() . " gerichtet");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

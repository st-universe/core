<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipAttackCycleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class AlertRedHelper implements AlertRedHelperInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipAttackCycleInterface $shipAttackCycle;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipAttackCycleInterface $shipAttackCycle
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipAttackCycle = $shipAttackCycle;
    }

    public function checkForAlertRedShips(ShipInterface $leadShip, &$informations) : array
    {
        $shipsToShuffle = [];
        
        // get ships inside or outside systems
        if ($leadShip->getSystem() !== null) {
            $starSystem = $leadShip->getSystem();
            $shipsOnLocation = $this->shipRepository->getByInnerSystemLocation($starSystem->getId(), $leadShip->getPosX(), $leadShip->getPosY());
        } else
        {
            $shipsOnLocation = $this->shipRepository->getByOuterSystemLocation($leadShip->getCx(), $leadShip->getCy());
        }

        $fleetIds = [];
        $fleetCount = 0;
        $singleShipCount = 0;
        
        foreach ($shipsOnLocation as $shipOnLocation) {

            // own ships dont count
            if ($shipOnLocation->getUser()->getId() === $leadShip->getUser()->getId())
            {
                continue;
            }
            
            // ships dont count if user is on vacation
            if ($shipOnLocation->getUser()->isVacationRequestOldEnough())
            {
                continue;
            }
            
            //ships of friends dont attack
            if ($shipOnLocation->getUser()->isFriend($leadShip->getUser()->getId()))
            {
                continue;
            }
            
            //cloaked ships dont attack
            if ($shipOnLocation->getCloakState())
            {
                continue;
            }
            
            //warped ships dont attack
            if ($shipOnLocation->getWarpState())
            {
                continue;
            }
            
            $fleet = $shipOnLocation->getFleet();
            
            if ($fleet === null) {
                if ($shipOnLocation->getAlertState() == ShipAlertStateEnum::ALERT_RED) {
                    $singleShipCount++;
                    $shipsToShuffle[$shipOnLocation->getId()] = $shipOnLocation;
                }
            }
            else {
                $fleetIdEintrag = $fleetIds[$fleet->getId()] ?? null;
                if ($fleetIdEintrag === null) {
                    if ($fleet->getLeadShip()->getAlertState() == ShipAlertStateEnum::ALERT_RED) {
                        $fleetCount++;
                        $shipsToShuffle[$fleet->getLeadShip()->getId()] = $fleet->getLeadShip();
                    }
                    $fleetIds[$fleet->getId()] = [];
                }
            }
        }
        
        if ($fleetCount == 1) {
            $informations[] = sprintf(_('In Sektor %d|%d befindet sich 1 Flotte auf [b][color=red]Alarm-Rot![/color][/b]') . "\n", $leadShip->getPosX(), $leadShip->getPosY());
        }
        if ($fleetCount > 1) {
            $informations[] = sprintf(_('In Sektor %d|%d befinden sich %d Flotte auf [b][color=red]Alarm-Rot![/color][/b]') . "\n", $leadShip->getPosX(), $leadShip->getPosY(), $fleetCount);
        }
        if ($singleShipCount == 1) {
            $informations[] = sprintf(_('In Sektor %d|%d befindet sich 1 Einzelschiff auf [b][color=red]Alarm-Rot![/color][/b]') . "\n", $leadShip->getPosX(), $leadShip->getPosY());
        }
        if ($singleShipCount > 1) {
            $informations[] = sprintf(_('In Sektor %d|%d befinden sich %d Einzelschiffe auf [b][color=red]Alarm-Rot![/color][/b]') . "\n", $leadShip->getPosX(), $leadShip->getPosY(), $singleShipCount);
        }

        return $shipsToShuffle;
    }

    public function performAttackCycle(ShipInterface $alertShip, ShipInterface $leadShip, &$informations) : void
    {
        $fleet = false;
        $target_user_id = $alertShip->getUserId();
        if ($alertShip->getFleetId()) {
            $attacker = [];

            // only uncloaked and unwarped ships enter fight
            foreach ($alertShip->getFleet()->getShips()->toArray() as $fleetShip)
            {
                if (!$fleetShip->getCloakState() && !$fleetShip->getWarpState())
                {
                    $attacker[] = $fleetShip;
                }
            }
            $fleet = true;
        } else {
            $attacker = [$alertShip->getId() => $alertShip];
        }
        if ($leadShip->isFleetLeader()) {
            $defender = [];

            // only uncloaked ships enter fight
            foreach ($leadShip->getFleet()->getShips()->toArray() as $defShip)
            {
                if (!$defShip->getCloakState())
                {
                    $defender[] = $defShip;
                }
            }
            // if whole flying fleet cloaked, nothing happens
            if (empty($defender))
            {
                return;
            }
            $fleet = true;
        } else {
            // if flying ship is cloaked, nothing happens
            if ($leadShip->getCloakState())
            {
                return;
            }

            $defender = [$leadShip->getId() => $leadShip];
        }
        $this->shipAttackCycle->init($attacker, $defender);
        $this->shipAttackCycle->cycle(true);

        $pm = sprintf(_('Eigene Schiffe auf [b][color=red]Alarm-Rot[/color][/b], Kampf in Sektor %d|%d') . "\n", $leadShip->getPosX(), $leadShip->getPosY());
        foreach ($this->shipAttackCycle->getMessages() as $key => $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send(
            (int)$leadShip->getUser()->getId(),
            (int)$target_user_id,
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        if ($leadShip->getIsDestroyed()) {

            $informations = array_merge($informations, $this->shipAttackCycle->getMessages());
            return;
        }

        $informations [] = sprintf(_('[b][color=red]Alarm-Rot[/color][/b] fremder Schiffe auf Feld %d|%d, Angriff durchgeführt') . "\n", $leadShip->getPosX(), $leadShip->getPosY());
        $informations = array_merge($informations, $this->shipAttackCycle->getMessages());
    }
}

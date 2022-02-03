<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
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

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipAttackCycleInterface $shipAttackCycle,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipAttackCycle = $shipAttackCycle;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function doItAll(ShipInterface $ship, ?GameControllerInterface $game, ?ShipInterface $tractoringShip = null): array
    {
        if (
            $tractoringShip !== null
            && $tractoringShip->getUser()->getId() === 102
        ) {
            $this->loggerUtil->init('arHelper', LoggerEnum::LEVEL_ERROR);
        }
        $informations = [];

        $shipsToShuffle = $this->checkForAlertRedShips($ship, $informations, $tractoringShip);
        shuffle($shipsToShuffle);
        foreach ($shipsToShuffle as $alertShip) {
            $this->performAttackCycle($alertShip, $ship, $informations);
        }

        if ($game !== null) {
            $game->addInformationMergeDown($informations);
            return [];
        } else {
            return $informations;
        }
    }

    public function checkForAlertRedShips(ShipInterface $leadShip, &$informations, ?ShipInterface $tractoringShip = null): array
    {
        if ($leadShip->getUser()->getId() === GameEnum::USER_NOONE) {
            return [];
        }
        if ($this->allFleetShipsWarped($leadShip)) {
            return [];
        }
        if ($this->allFleetShipsCloaked($leadShip)) {
            return [];
        }

        $shipsToShuffle = [];

        // get ships inside or outside systems
        $shipsOnLocation = $this->shipRepository->getShipsForAlertRed($leadShip);

        $fleetIds = [];
        $fleetCount = 0;
        $singleShipCount = 0;
        $usersToInformAboutTrojanHorse = [];

        foreach ($shipsOnLocation as $shipOnLocation) {

            //ships of friends from tractoring ship dont attack
            if ($tractoringShip !== null &&  $shipOnLocation->getUser()->isFriend($tractoringShip->getUser()->getId())) {
                $this->loggerUtil->log('A');
                $user = $shipOnLocation->getUser();
                $userId = $user->getId();

                if (
                    !array_key_exists($userId, $usersToInformAboutTrojanHorse)
                    && $user !== $leadShip->getUser()
                    && !$user->isFriend($leadShip->getUser()->getId())
                ) {
                    $txt = sprintf(
                        _('Die %s von Spieler %s ist in Sektor %s eingeflogen und hat dabei die %s von Spieler %s gezogen'),
                        $tractoringShip->getName(),
                        $tractoringShip->getUser()->getName(),
                        $tractoringShip->getSectorString(),
                        $leadShip->getName(),
                        $leadShip->getUser()->getName()
                    );
                    $usersToInformAboutTrojanHorse[$userId] = $txt;
                }
                continue;
            }

            //ships of friends dont attack
            if ($shipOnLocation->getUser()->isFriend($leadShip->getUser()->getId())) {
                continue;
            }

            $fleet = $shipOnLocation->getFleet();

            if ($fleet === null) {
                $singleShipCount++;
                $shipsToShuffle[$shipOnLocation->getId()] = $shipOnLocation;
            } else {
                $fleetIdEintrag = $fleetIds[$fleet->getId()] ?? null;
                if ($fleetIdEintrag === null) {
                    $fleetCount++;
                    $shipsToShuffle[$fleet->getLeadShip()->getId()] = $fleet->getLeadShip();
                    $fleetIds[$fleet->getId()] = [];
                }
            }
        }

        $this->informAboutTrojanHorse($usersToInformAboutTrojanHorse);

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

    private function informAboutTrojanHorse(array $users): void
    {
        foreach ($users as $userId => $txt) {
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $userId,
                $txt
            );
        }
    }

    private function allFleetShipsWarped(ShipInterface $leadShip): bool
    {
        if ($leadShip->getFleet() !== null) {
            foreach ($leadShip->getFleet()->getShips() as $fleetShip) {
                if (!$fleetShip->getWarpState()) {
                    return false;
                }
            }
        } else {
            if (!$leadShip->getWarpState()) {
                return false;
            }
        }

        return true;
    }

    private function allFleetShipsCloaked(ShipInterface $leadShip): bool
    {
        if ($leadShip->getFleet() !== null) {
            foreach ($leadShip->getFleet()->getShips() as $fleetShip) {
                if (!$fleetShip->getCloakState()) {
                    return false;
                }
            }
        } else {
            if (!$leadShip->getCloakState()) {
                return false;
            }
        }

        return true;
    }

    public function performAttackCycle(ShipInterface $alertShip, ShipInterface $leadShip, &$informations, $isColonyDefense = false): void
    {
        $alert_user_id = $alertShip->getUser()->getId();
        $lead_user_id = $leadShip->getUser()->getId();
        $isAlertShipBase = $alertShip->isBase();

        if ($alertShip->getFleetId()) {
            $attacker = [];

            // only uncloaked and unwarped ships enter fight
            foreach ($alertShip->getFleet()->getShips()->toArray() as $fleetShip) {
                if (!$fleetShip->getCloakState() && !$fleetShip->getWarpState()) {
                    $attacker[$fleetShip->getId()] = $fleetShip;
                }
            }
        } else {
            $attacker = [$alertShip->getId() => $alertShip];
        }
        if ($leadShip->isFleetLeader()) {
            $defender = [];

            // only uncloaked ships enter fight
            foreach ($leadShip->getFleet()->getShips()->toArray() as $defShip) {
                if (!$defShip->getCloakState()) {
                    $defender[$defShip->getId()] = $defShip;
                }
            }
            // if whole flying fleet cloaked, nothing happens
            if (empty($defender)) {
                return;
            }
        } else {
            // if flying ship is cloaked, nothing happens
            if ($leadShip->getCloakState()) {
                return;
            }

            $defender = [$leadShip->getId() => $leadShip];
        }
        $this->shipAttackCycle->init($attacker, $defender);
        $this->shipAttackCycle->cycle(true);

        $pm = sprintf(_('Eigene Schiffe auf [b][color=red]%s[/color][/b], Kampf in Sektor %d|%d') . "\n", $isColonyDefense ? 'Kolonie-Verteidigung' : 'Alarm-Rot', $leadShip->getPosX(), $leadShip->getPosY());
        foreach ($this->shipAttackCycle->getMessages() as $value) {
            $pm .= $value . "\n";
        }
        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $alertShip->getId());
        $this->privateMessageSender->send(
            (int) $lead_user_id,
            (int) $alert_user_id,
            $pm,
            $isAlertShipBase ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $alertShip->getIsDestroyed() ? null : $href
        );
        $pm = sprintf(_('Fremde Schiffe auf [b][color=red]%s[/color][/b], Kampf in Sektor %d|%d') . "\n", $isColonyDefense ? 'Kolonie-Verteidigung' : 'Alarm-Rot', $leadShip->getPosX(), $leadShip->getPosY());
        foreach ($this->shipAttackCycle->getMessages() as $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send(
            (int) $alert_user_id,
            (int) $lead_user_id,
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        if ($leadShip->getIsDestroyed()) {

            $informations = array_merge($informations, $this->shipAttackCycle->getMessages());
            return;
        }

        $informations[] = sprintf(_('[b][color=red]%s[/color][/b] fremder Schiffe auf Feld %d|%d, Angriff durchgeführt') . "\n", $isColonyDefense ? 'Kolonie-Verteidigung' : 'Alarm-Rot', $leadShip->getPosX(), $leadShip->getPosY());
        $informations = array_merge($informations, $this->shipAttackCycle->getMessages());
    }
}

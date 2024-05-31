<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Player\PlayerRelationDeterminatorInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

//TODO create unit tests
final class AlertRedHelper implements AlertRedHelperInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipAttackCycleInterface $shipAttackCycle;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private PrivateMessageSenderInterface $privateMessageSender;

    private LoggerUtilInterface $loggerUtil;

    private PlayerRelationDeterminatorInterface $playerRelationDeterminator;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipAttackCycleInterface $shipAttackCycle,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipAttackCycle = $shipAttackCycle;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->playerRelationDeterminator = $playerRelationDeterminator;
    }

    public function doItAll(
        ShipInterface $ship,
        InformationInterface $informations,
        ?ShipInterface $tractoringShip = null
    ): void {
        //$this->loggerUtil->init('ARH', LoggerEnum::LEVEL_ERROR);

        $shipsToShuffle = $this->checkForAlertRedShips($ship, $informations, $tractoringShip);
        shuffle($shipsToShuffle);

        $ships = $this->getShips($ship);

        foreach ($shipsToShuffle as $alertShip) {
            $leader = $this->getLeader($ships);
            if ($leader !== null) {
                $this->loggerUtil->log(sprintf('leaderId: %d', $leader->getId()));
            } else {
                $this->loggerUtil->log('leader is null');
            }

            if ($leader === null) {
                break;
            }

            $this->performAttackCycle($alertShip, $leader, $informations);
        }
    }

    /**
     * @param ShipWrapperInterface[] $wrappers
     */
    private function getLeader(array $wrappers): ?ShipInterface
    {
        $nonDestroyedShips = [];

        foreach ($wrappers as $wrapper) {
            $ship = $wrapper->get();

            if (!$ship->isDestroyed()) {
                if ($ship->isFleetLeader()) {
                    return $ship;
                }
                $nonDestroyedShips[] = $ship;
            }
        }

        if ($nonDestroyedShips !== []) {
            return current($nonDestroyedShips);
        }

        return null;
    }

    /**
     * @return ShipWrapperInterface[]
     */
    private function getShips(ShipInterface $leadShip): array
    {
        if ($leadShip->getFleet() !== null) {
            return $this->shipWrapperFactory->wrapShips($leadShip->getFleet()->getShips()->toArray());
        } else {
            return $this->shipWrapperFactory->wrapShips([$leadShip]);
        }
    }

    /**
     * @return array<ShipInterface>
     */
    private function checkForAlertRedShips(
        ShipInterface $leadShip,
        InformationInterface $informations,
        ?ShipInterface $tractoringShip = null
    ): array {
        $leadShipUser = $leadShip->getUser();

        if ($leadShipUser->getId() === UserEnum::USER_NOONE) {
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

            if ($this->skipShipOnLocation($leadShip, $shipOnLocation, $tractoringShip, $usersToInformAboutTrojanHorse)) {
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
        $this->addAlertRedInfo($leadShip, $singleShipCount, $fleetCount, $informations);

        return $shipsToShuffle;
    }

    /**
     * @param array<int, string> $users
     */
    private function informAboutTrojanHorse(array $users): void
    {
        foreach ($users as $userId => $txt) {
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $userId,
                $txt
            );
        }
    }

    private function addAlertRedInfo(
        ShipInterface $leadShip,
        int $singleShipCount,
        int $fleetCount,
        InformationInterface $informations
    ): void {
        if ($fleetCount == 1) {
            $informations->addInformation(sprintf(
                _('In Sektor %d|%d befindet sich 1 Flotte auf [b][color=red]Alarm-Rot![/color][/b]') . "\n",
                $leadShip->getPosX(),
                $leadShip->getPosY()
            ));
        }
        if ($fleetCount > 1) {
            $informations->addInformation(sprintf(
                _('In Sektor %d|%d befinden sich %d Flotte auf [b][color=red]Alarm-Rot![/color][/b]') . "\n",
                $leadShip->getPosX(),
                $leadShip->getPosY(),
                $fleetCount
            ));
        }
        if ($singleShipCount == 1) {
            $informations->addInformation(sprintf(
                _('In Sektor %d|%d befindet sich 1 Einzelschiff auf [b][color=red]Alarm-Rot![/color][/b]') . "\n",
                $leadShip->getPosX(),
                $leadShip->getPosY()
            ));
        }
        if ($singleShipCount > 1) {
            $informations->addInformation(sprintf(
                _('In Sektor %d|%d befinden sich %d Einzelschiffe auf [b][color=red]Alarm-Rot![/color][/b]') . "\n",
                $leadShip->getPosX(),
                $leadShip->getPosY(),
                $singleShipCount
            ));
        }
    }

    private function allFleetShipsWarped(ShipInterface $leadShip): bool
    {
        $fleet = $leadShip->getFleet();
        if ($fleet !== null) {
            foreach ($fleet->getShips() as $fleetShip) {
                if (!$fleetShip->getWarpState()) {
                    return false;
                }
            }
        } elseif (!$leadShip->getWarpState()) {
            return false;
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
        } elseif (!$leadShip->getCloakState()) {
            return false;
        }

        return true;
    }

    /** @param array<int, string> $usersToInformAboutTrojanHorse */
    private function skipShipOnLocation(
        ShipInterface $leadShip,
        ShipInterface $shipOnLocation,
        ?ShipInterface $tractoringShip,
        array &$usersToInformAboutTrojanHorse
    ): bool {
        $user = $shipOnLocation->getUser();
        $leadShipUser = $leadShip->getUser();

        //ships of friends from tractoring ship dont attack
        if ($tractoringShip !== null && $this->playerRelationDeterminator->isFriend($user, $tractoringShip->getUser())) {
            $userId = $user->getId();

            if (
                !array_key_exists($userId, $usersToInformAboutTrojanHorse)
                && $user !== $leadShipUser
                && !$this->playerRelationDeterminator->isFriend($user, $leadShipUser)
            ) {
                $txt = sprintf(
                    _('Die %s von Spieler %s ist in Sektor %s eingeflogen und hat dabei die %s von Spieler %s gezogen'),
                    $tractoringShip->getName(),
                    $tractoringShip->getUser()->getName(),
                    $tractoringShip->getSectorString(),
                    $leadShip->getName(),
                    $leadShipUser->getName()
                );
                $usersToInformAboutTrojanHorse[$userId] = $txt;
            }
            return true;
        }

        //ships of friends dont attack
        if ($this->playerRelationDeterminator->isFriend($user, $leadShipUser)) {
            return true;
        }

        //ships in finished tholian web dont attack
        if ($shipOnLocation->getHoldingWeb() !== null && $shipOnLocation->getHoldingWeb()->isFinished()) {
            return true;
        }

        if ($this->skipDueToPirateProtection(
            $leadShipUser,
            $user,
            $shipOnLocation
        )) {
            return true;
        }

        return false;
    }

    private function skipDueToPirateProtection(UserInterface $leadShipUser, UserInterface $user, ShipInterface $shipOnLocation): bool
    {
        //pirates don't attack if user is protected
        $pirateWrath = $leadShipUser->getPirateWrath();
        if (
            $shipOnLocation->getUserId() === UserEnum::USER_NPC_KAZON
            && $pirateWrath !== null
            && $pirateWrath->getProtectionTimeout() > time()
        ) {
            return true;
        }

        //players don't attack pirates if protection is active
        $pirateWrath = $user->getPirateWrath();
        if (
            $leadShipUser->getId() === UserEnum::USER_NPC_KAZON
            && $pirateWrath !== null
            && $pirateWrath->getProtectionTimeout() > time()
        ) {
            return true;
        }

        return false;
    }

    public function performAttackCycle(
        ShipInterface $alertShip,
        ShipInterface $leadShip,
        InformationInterface $informations,
        bool $isColonyDefense = false
    ): void {
        $alert_user_id = $alertShip->getUser()->getId();
        $lead_user_id = $leadShip->getUser()->getId();
        $isAlertShipBase = $alertShip->isBase();

        if ($alertShip->getFleet() !== null) {
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
            $this->loggerUtil->log('leadShip is FleetLeader');
            $defender = [];

            // only uncloaked ships enter fight
            foreach ($leadShip->getFleet()->getShips()->toArray() as $defShip) {
                if (!$defShip->getCloakState()) {
                    $defender[$defShip->getId()] = $defShip;
                }
            }
            // if whole flying fleet cloaked, nothing happens
            if ($defender === []) {
                return;
            }
        } else {
            // if flying ship is cloaked, nothing happens
            if ($leadShip->getCloakState()) {
                return;
            }

            $defender = [$leadShip->getId() => $leadShip];
        }

        //$this->loggerUtil->log(sprintf('before_shipAttackCycle, attackerCount: %d, defenderCount: %d', count($attacker), count($defender)));

        $messageCollection = $this->shipAttackCycle->cycle(
            $this->shipWrapperFactory->wrapShips($attacker),
            $this->shipWrapperFactory->wrapShips($defender),
            false,
            true
        );

        $fightInformations = $messageCollection->getInformationDump();

        $pm = sprintf(
            _("Eigene Schiffe auf [b][color=red]%s[/color][/b], Kampf in Sektor %s\n%s"),
            $isColonyDefense ? 'Kolonie-Verteidigung' : 'Alarm-Rot',
            $leadShip->getSectorString(),
            $fightInformations->getInformationsAsString()
        );
        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $alertShip->getId());
        $this->privateMessageSender->send(
            $lead_user_id,
            $alert_user_id,
            $pm,
            $isAlertShipBase ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $alertShip->isDestroyed() ? null : $href
        );
        $pm = sprintf(
            _("Fremde Schiffe auf [b][color=red]%s[/color][/b], Kampf in Sektor %s\n%s"),
            $isColonyDefense ? 'Kolonie-Verteidigung' : 'Alarm-Rot',
            $leadShip->getSectorString(),
            $fightInformations->getInformationsAsString()
        );
        $this->privateMessageSender->send(
            $alert_user_id,
            $lead_user_id,
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        if ($leadShip->isDestroyed()) {
            $fightInformations->dumpTo($informations);
            return;
        }

        $informations->addInformation(sprintf(
            _('[b][color=red]%s[/color][/b] fremder Schiffe auf Feld %d|%d, Angriff durchgeführt') . "\n",
            $isColonyDefense ? 'Kolonie-Verteidigung' : 'Alarm-Rot',
            $leadShip->getPosX(),
            $leadShip->getPosY()
        ));
        $fightInformations->dumpTo($informations);
    }
}

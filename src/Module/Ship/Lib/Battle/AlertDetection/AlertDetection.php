<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\ArrayCollection;
use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Orm\Entity\ShipInterface;

class AlertDetection implements AlertDetectionInterface
{
    public function __construct(
        private AlertedShipsDetectionInterface $alertedShipsDetection,
        private SkipDetectionInterface $skipDetection,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private TrojanHorseNotifierInterface $trojanHorseNotifier,
        private AlertedShipInformationInterface $alertedShipInformation
    ) {}

    #[Override]
    public function detectAlertedBattleParties(
        ShipInterface $incomingShip,
        InformationInterface $informations,
        ?ShipInterface $tractoringShip = null
    ): array {

        $alertedWrappers = $this->alertedShipsDetection->getAlertedShipsOnLocation(
            $incomingShip->getLocation(),
            $incomingShip->getUser()
        );
        if ($alertedWrappers->isEmpty()) {
            return [];
        }

        /** @var array<int, AlertStateBattleParty> */
        $battleParties = [];
        $usersToInformAboutTrojanHorse = new ArrayCollection();

        foreach ($alertedWrappers as $alertedWrapper) {

            $alertedShip = $alertedWrapper->get();

            if ($this->skipDetection->isSkipped($incomingShip, $alertedShip, $tractoringShip, $usersToInformAboutTrojanHorse)) {
                continue;
            }

            $battleParties[$alertedShip->getId()] = $this->battlePartyFactory->createAlertStateBattleParty($alertedWrapper);
        }

        $this->trojanHorseNotifier->informUsersAboutTrojanHorse(
            $incomingShip,
            $tractoringShip,
            $usersToInformAboutTrojanHorse
        );

        $this->alertedShipInformation->addAlertedShipsInfo(
            $incomingShip,
            $battleParties,
            $informations
        );

        return $battleParties;
    }
}

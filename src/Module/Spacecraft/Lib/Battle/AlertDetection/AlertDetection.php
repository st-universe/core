<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\ArrayCollection;
use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AlertStateBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Orm\Entity\SpacecraftInterface;

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
        SpacecraftInterface $incomingSpacecraft,
        InformationInterface $informations,
        ?SpacecraftInterface $tractoringSpacecraft = null
    ): array {

        $alertedWrappers = $this->alertedShipsDetection->getAlertedShipsOnLocation(
            $incomingSpacecraft->getLocation(),
            $incomingSpacecraft->getUser()
        );
        if ($alertedWrappers->isEmpty()) {
            return [];
        }

        /** @var array<int, AlertStateBattleParty> */
        $battleParties = [];
        $usersToInformAboutTrojanHorse = new ArrayCollection();

        foreach ($alertedWrappers as $alertedWrapper) {

            $alertedShip = $alertedWrapper->get();

            if ($this->skipDetection->isSkipped($incomingSpacecraft, $alertedShip, $tractoringSpacecraft, $usersToInformAboutTrojanHorse)) {
                continue;
            }

            $battleParties[$alertedShip->getId()] = $this->battlePartyFactory->createAlertStateBattleParty($alertedWrapper);
        }

        $this->trojanHorseNotifier->informUsersAboutTrojanHorse(
            $incomingSpacecraft,
            $tractoringSpacecraft,
            $usersToInformAboutTrojanHorse
        );

        $this->alertedShipInformation->addAlertedShipsInfo(
            $incomingSpacecraft,
            $battleParties,
            $informations
        );

        return $battleParties;
    }
}

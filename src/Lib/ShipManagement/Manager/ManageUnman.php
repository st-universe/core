<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use RuntimeException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\Crew\ShipLeaverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class ManageUnman implements ManagerInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private ShipLeaverInterface $shipLeaver;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        ShipRepositoryInterface $shipRepository,
        TroopTransferUtilityInterface $troopTransferUtility,
        ShipLeaverInterface $shipLeaver
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->shipRepository = $shipRepository;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->shipLeaver = $shipLeaver;
    }

    public function manage(ShipWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $msg = [];

        $unman = $values['unman'] ?? null;
        if ($unman === null) {
            throw new RuntimeException('value array not existent');
        }

        $ship = $wrapper->get();
        $user = $managerProvider->getUser();

        $ownCrewCount = $this->troopTransferUtility->ownCrewOnTarget($user, $ship);

        if (
            isset($unman[$ship->getId()])
            && $ship->getUser() === $user
            && $ownCrewCount > 0
        ) {
            //check if there is enough space for crew on colony
            if (!$managerProvider->isAbleToStoreCrew($ownCrewCount)) {
                $msg[] = sprintf(
                    _('%s: Nicht genügend Platz für die Crew auf der %s'),
                    $ship->getName(),
                    $managerProvider->getName()
                );
                return $msg;
            }

            $this->dumpForeignCrew($ship);

            $managerProvider->addCrewAssignments($ship->getCrewlist());
            $ship->getCrewlist()->clear();
            $msg[] = sprintf(
                _('%s: Die Crew wurde runtergebeamt'),
                $ship->getName()
            );

            foreach ($ship->getDockedShips() as $dockedShip) {
                $dockedShip->setDockedTo(null);
                $this->shipRepository->save($dockedShip);
            }
            $ship->getDockedShips()->clear();

            $this->shipSystemManager->deactivateAll($wrapper);

            $ship->setAlertStateGreen();
        }

        return $msg;
    }

    private function dumpForeignCrew(ShipInterface $ship): void
    {
        foreach ($ship->getCrewlist() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser() !== $ship->getUser()) {
                $this->shipLeaver->dumpCrewman(
                    $shipCrew,
                    sprintf(
                        'Die Dienste von Crewman %s werden nicht mehr auf der Station %s von Spieler %s benötigt.',
                        $shipCrew->getCrew()->getName(),
                        $ship->getName(),
                        $ship->getUser()->getName(),
                    )
                );
            }
        }
    }
}

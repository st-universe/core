<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use RuntimeException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class ManageUnman implements ManagerInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->shipRepository = $shipRepository;
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

        if (
            isset($unman[$ship->getId()])
            && $ship->getUser() === $user
            && $ship->getCrewCount() > 0
        ) {

            //check if there is enough space for crew on colony
            if (!$managerProvider->isAbleToStoreCrew($ship->getCrewCount())) {
                $msg[] = sprintf(
                    _('%s: Nicht genügend Platz für die Crew auf der %s'),
                    $ship->getName(),
                    $managerProvider->getName()
                );
                return $msg;
            }

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
}

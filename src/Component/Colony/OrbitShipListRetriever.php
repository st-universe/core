<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Override;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

/**
 * Retrieve all ships within the orbit of a colony
 */
final class OrbitShipListRetriever implements OrbitShipListRetrieverInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository)
    {
    }

    #[Override]
    public function retrieve(ColonyInterface $colony): array
    {
        $result = [];

        $shipList = $this->shipRepository->getByLocation($colony->getStarsystemMap());

        foreach ($shipList as $ship) {
            $fleetId = (int) $ship->getFleetId();

            if (!array_key_exists($fleetId, $result)) {
                if ($fleetId === 0) {
                    $name = 'Einzelschiffe';
                } else {
                    /** @var FleetInterface $fleet */
                    $fleet = $ship->getFleet();
                    $name = $fleet->getName();
                }

                $result[$fleetId] = ['ships' => [], 'name' => $name];
            }

            $result[$fleetId]['ships'][$ship->getId()] = $ship;
        }

        return $result;
    }
}

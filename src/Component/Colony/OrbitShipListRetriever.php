<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Override;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

/**
 * Retrieve all spacecrafts within the orbit of a colony
 */
final class OrbitShipListRetriever implements OrbitShipListRetrieverInterface
{
    public function __construct(private SpacecraftRepositoryInterface $spacecraftRepository) {}

    #[Override]
    public function retrieve(ColonyInterface $colony): array
    {
        $result = [];

        $spacecraftList = $this->spacecraftRepository->getByLocation($colony->getStarsystemMap());

        foreach ($spacecraftList as $spacecraft) {
            $this->addShip($spacecraft, $result);
        }

        return $result;
    }

    /** @param array<int, array{ships: array<int, SpacecraftInterface>, name: string}> $fleetArray */
    private function addShip(SpacecraftInterface $spacecraft, array &$fleetArray): void
    {
        $fleet = $spacecraft instanceof ShipInterface ? $spacecraft->getFleet() : null;
        $fleetId = $fleet === null ? 0 : $fleet->getId();

        if (!array_key_exists($fleetId, $fleetArray)) {
            $name = $fleet === null ? 'Einzelschiffe' : $fleet->getName();

            $fleetArray[$fleetId] = ['ships' => [], 'name' => $name];
        }

        $fleetArray[$fleetId]['ships'][$spacecraft->getId()] = $spacecraft;
    }
}

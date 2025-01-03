<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

/**
 * Retrieve all ship wrappers within the orbit of a colony
 */
final class OrbitShipWrappersRetriever implements OrbitShipWrappersRetrieverInterface
{
    public function __construct(
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function retrieve(ColonyInterface $colony): Collection
    {
        $shipsOnLocation = $colony->getLocation()
            ->getSpacecrafts()
            ->filter(fn(SpacecraftInterface $spacecraft): bool => $spacecraft instanceof ShipInterface
                && !$spacecraft->isCloaked());

        return $this->spacecraftWrapperFactory->wrapSpacecraftsAsGroups($shipsOnLocation);
    }
}

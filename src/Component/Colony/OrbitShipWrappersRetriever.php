<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

/**
 * Retrieve all ship wrappers within the orbit of a colony
 */
final class OrbitShipWrappersRetriever implements OrbitShipWrappersRetrieverInterface
{
    public function __construct(
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function retrieve(Colony $colony): Collection
    {
        $shipsOnLocation = $colony->getLocation()
            ->getSpacecrafts()
            ->filter(fn(Spacecraft $spacecraft): bool => $spacecraft instanceof Ship
                && !$spacecraft->isCloaked());

        return $this->spacecraftWrapperFactory->wrapSpacecraftsAsGroups($shipsOnLocation);
    }
}

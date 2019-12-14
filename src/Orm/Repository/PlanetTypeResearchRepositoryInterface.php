<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PlanetTypeInterface;
use Stu\Orm\Entity\PlanetTypeResearchInterface;

interface PlanetTypeResearchRepositoryInterface extends ObjectRepository
{
    /**
     * @return PlanetTypeResearchInterface[]
     */
    public function getByPlanetType(PlanetTypeInterface $planetType): array;
}

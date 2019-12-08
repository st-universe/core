<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PlanetTypeInterface;

final class PlanetTypeResearchRepository extends EntityRepository implements PlanetTypeResearchRepositoryInterface
{
    public function getByPlanetType(PlanetTypeInterface $planetType): array
    {
        return $this->findBy([
            'planet_type_id' => $planetType
        ]);
    }
}

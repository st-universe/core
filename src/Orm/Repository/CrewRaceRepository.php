<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\CrewRace;

/**
 * @extends EntityRepository<CrewRace>
 */
final class CrewRaceRepository extends EntityRepository implements CrewRaceRepositoryInterface
{
    public function getByFaction(int $factionId): array
    {
        return $this->findBy([
            'faction_id' => $factionId
        ]);
    }
}

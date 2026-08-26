<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewRace;

/**
 * @extends ObjectRepository<CrewRace>
 *
 * @method null|CrewRace find(integer $id)
 */
interface CrewRaceRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<CrewRace>
     */
    public function getByFaction(int $factionId): array;
}

<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\CrewRaceInterface;

/**
 * @extends ObjectRepository<CrewRace>
 *
 * @method null|CrewRaceInterface find(integer $id)
 */
interface CrewRaceRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<CrewRaceInterface>
     */
    public function getByFaction(int $factionId): array;
}

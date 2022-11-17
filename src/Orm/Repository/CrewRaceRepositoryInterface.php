<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewRaceInterface;

/**
 * @method null|CrewRaceInterface find(integer $id)
 */
interface CrewRaceRepositoryInterface extends ObjectRepository
{
    /**
     * @return CrewRaceInterface[]
     */
    public function getByFaction(int $factionId): array;
}

<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewRaceInterface;

interface CrewRaceRepositoryInterface extends ObjectRepository
{
    /**
     * @return CrewRaceInterface[]
     */
    public function getByFaction(int $factionId): array;
}
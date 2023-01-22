<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildplanHangar;
use Stu\Orm\Entity\BuildplanHangarInterface;

/**
 * @extends ObjectRepository<BuildplanHangar>
 */
interface BuildplanHangarRepositoryInterface extends ObjectRepository
{
    public function getByRump(int $rumpId): ?BuildplanHangarInterface;
}

<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildplanHangar;

/**
 * @extends ObjectRepository<BuildplanHangar>
 */
interface BuildplanHangarRepositoryInterface extends ObjectRepository
{
    public function getByRump(int $rumpId): ?BuildplanHangar;
}

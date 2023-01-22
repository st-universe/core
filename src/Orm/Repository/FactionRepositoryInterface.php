<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\FactionInterface;

/**
 * @extends ObjectRepository<Faction>
 *
 * @method null|FactionInterface find(integer $id)
 * @method FactionInterface[] findAll()
 */
interface FactionRepositoryInterface extends ObjectRepository
{
    /**
     * @return FactionInterface[]
     */
    public function getByChooseable(bool $chooseable): array;
}

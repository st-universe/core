<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\FactionInterface;

/**
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

<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LayerInterface;

/**
 * @method null|LayerInterface find(integer $id)
 */
interface LayerRepositoryInterface extends ObjectRepository
{
    /**
     * @return LayerInterface[]
     */
    public function findAllIndexed(): array;
}

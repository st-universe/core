<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AnomalyType;
use Stu\Orm\Entity\AnomalyTypeInterface;

/**
 * @extends ObjectRepository<AnomalyType>
 *
 * @method null|AnomalyTypeInterface find(integer $id)
 * @method AnomalyTypeInterface[] findAll()
 */
interface AnomalyTypeRepositoryInterface extends ObjectRepository
{
    public function prototype(): AnomalyTypeInterface;

    public function save(AnomalyTypeInterface $anomalytype): void;
}

<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AnomalyType;

/**
 * @extends ObjectRepository<AnomalyType>
 *
 * @method null|AnomalyType find(integer $id)
 * @method AnomalyType[] findAll()
 */
interface AnomalyTypeRepositoryInterface extends ObjectRepository
{
    public function prototype(): AnomalyType;

    public function save(AnomalyType $anomalytype): void;
}

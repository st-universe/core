<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyInterface;

/**
 * @extends ObjectRepository<Anomaly>
 *
 * @method null|AnomalyInterface find(integer $id)
 * @method AnomalyInterface[] findAll()
 */
interface AnomalyRepositoryInterface extends ObjectRepository
{
    public function prototype(): AnomalyInterface;

    public function save(AnomalyInterface $anomaly): void;
}

<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RepairTaskInterface;

/**
 * @method null|RepairTaskInterface find(integer $id)
 */
interface RepairTaskRepositoryInterface extends ObjectRepository
{
    public function prototype(): RepairTaskInterface;

    public function save(RepairTaskInterface $obj): void;
}

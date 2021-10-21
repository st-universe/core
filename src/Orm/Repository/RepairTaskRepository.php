<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\RepairTaskInterface;

final class RepairTaskRepository extends EntityRepository implements RepairTaskRepositoryInterface
{
    public function prototype(): RepairTaskInterface
    {
        return new RepairTask();
    }

    public function save(RepairTaskInterface $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }
}

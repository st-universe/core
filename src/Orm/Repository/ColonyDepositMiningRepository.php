<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonyDepositMining;
use Stu\Orm\Entity\ColonyDepositMiningInterface;

final class ColonyDepositMiningRepository extends EntityRepository implements ColonyDepositMiningRepositoryInterface
{
    public function prototype(): ColonyDepositMiningInterface
    {
        return new ColonyDepositMining();
    }

    public function save(ColonyDepositMiningInterface $entity): void
    {
        $em = $this->getEntityManager();

        $em->persist($entity);
    }
}

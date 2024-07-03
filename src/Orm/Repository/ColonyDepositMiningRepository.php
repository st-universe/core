<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ColonyDepositMining;
use Stu\Orm\Entity\ColonyDepositMiningInterface;

/**
 * @extends EntityRepository<ColonyDepositMining>
 */
final class ColonyDepositMiningRepository extends EntityRepository implements ColonyDepositMiningRepositoryInterface
{
    #[Override]
    public function prototype(): ColonyDepositMiningInterface
    {
        return new ColonyDepositMining();
    }

    #[Override]
    public function save(ColonyDepositMiningInterface $entity): void
    {
        $em = $this->getEntityManager();

        $em->persist($entity);
    }
}

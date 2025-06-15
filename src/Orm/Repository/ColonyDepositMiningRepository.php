<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ColonyDepositMining;
use Stu\Orm\Entity\ColonyDepositMiningInterface;
use Stu\Orm\Entity\ColonyInterface;

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

    #[Override]
    public function getCurrentUserDepositMinings(ColonyInterface $colony): array
    {
        $result = [];

        foreach (
            $this->findBy(
                [
                    'user' => $colony->getUser(),
                    'colony' => $colony
                ],
                ['commodity_id' => 'ASC']
            ) as $deposit
        ) {
            $result[$deposit->getCommodity()->getId()] = $deposit;
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonyDepositMining;
use Stu\Orm\Entity\Colony;

/**
 * @extends EntityRepository<ColonyDepositMining>
 */
final class ColonyDepositMiningRepository extends EntityRepository implements ColonyDepositMiningRepositoryInterface
{
    #[\Override]
    public function prototype(): ColonyDepositMining
    {
        return new ColonyDepositMining();
    }

    #[\Override]
    public function save(ColonyDepositMining $entity): void
    {
        $em = $this->getEntityManager();

        $em->persist($entity);
    }

    #[\Override]
    public function getCurrentUserDepositMinings(Colony $colony): array
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

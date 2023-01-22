<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ModuleQueue;
use Stu\Orm\Entity\ModuleQueueInterface;

/**
 * @extends EntityRepository<ModuleQueue>
 */
final class ModuleQueueRepository extends EntityRepository implements ModuleQueueRepositoryInterface
{
    public function getByUser(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT mq FROM %s mq
                JOIN %s c
                WITH mq.colony_id = c.id
                WHERE c.user_id = :userId',
                ModuleQueue::class,
                Colony::class
            )
        )->setParameters([
            'userId' => $userId
        ])->getResult();
    }

    public function getByColony(int $colonyId): array
    {
        return $this->findBy([
            'colony_id' => $colonyId
        ]);
    }

    public function getByColonyAndModuleAndBuilding(
        int $colonyId,
        int $moduleId,
        int $buildingFunction
    ): ?ModuleQueueInterface {
        return $this->findOneBy([
            'colony_id' => $colonyId,
            'module_id' => $moduleId,
            'buildingfunction' => $buildingFunction,
        ]);
    }

    public function getByColonyAndBuilding(
        int $colonyId,
        array $buildingFunctions
    ): array {
        return $this->findBy([
            'colony_id' => $colonyId,
            'buildingfunction' => $buildingFunctions
        ]);
    }

    public function prototype(): ModuleQueueInterface
    {
        return new ModuleQueue();
    }

    public function save(ModuleQueueInterface $moduleQueue): void
    {
        $em = $this->getEntityManager();

        $em->persist($moduleQueue);
    }

    public function delete(ModuleQueueInterface $moduleQueue): void
    {
        $em = $this->getEntityManager();

        $em->remove($moduleQueue);
        //$em->flush();
    }

    public function getAmountByColonyAndModule(int $colonyId, int $moduleId): int
    {
        $entry = $this->findOneBy([
            'colony_id' => $colonyId,
            'module_id' => $moduleId,
        ]);

        if ($entry !== null) {
            return $entry->getAmount();
        }
        return 0;
    }
}

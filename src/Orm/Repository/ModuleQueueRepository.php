<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ModuleQueue;

/**
 * @extends EntityRepository<ModuleQueue>
 */
final class ModuleQueueRepository extends EntityRepository implements ModuleQueueRepositoryInterface
{
    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT mq FROM %s mq
                    JOIN %s c
                    WITH mq.colony_id = c.id
                    WHERE c.user_id = :userId',
                    ModuleQueue::class,
                    Colony::class
                )
            )
            ->setParameters([
                'userId' => $userId
            ])
            ->getResult();
    }

    #[Override]
    public function getByColony(int $colonyId): array
    {
        return $this->findBy([
            'colony_id' => $colonyId
        ]);
    }

    #[Override]
    public function getByColonyAndModuleAndBuilding(
        int $colonyId,
        int $moduleId,
        int $buildingFunction
    ): ?ModuleQueue {
        return $this->findOneBy([
            'colony_id' => $colonyId,
            'module_id' => $moduleId,
            'buildingfunction' => $buildingFunction,
        ]);
    }

    #[Override]
    public function getByColonyAndBuilding(
        int $colonyId,
        array $buildingFunctions
    ): array {
        return $this->findBy([
            'colony_id' => $colonyId,
            'buildingfunction' => $buildingFunctions
        ]);
    }

    #[Override]
    public function prototype(): ModuleQueue
    {
        return new ModuleQueue();
    }

    #[Override]
    public function save(ModuleQueue $moduleQueue): void
    {
        $em = $this->getEntityManager();

        $em->persist($moduleQueue);
    }

    #[Override]
    public function delete(ModuleQueue $moduleQueue): void
    {
        $em = $this->getEntityManager();

        $em->remove($moduleQueue);
    }

    #[Override]
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

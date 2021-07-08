<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ModuleQueue;
use Stu\Orm\Entity\ModuleQueueInterface;

final class ModuleQueueRepository extends EntityRepository implements ModuleQueueRepositoryInterface
{
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
        //$em->flush();
    }

    public function delete(ModuleQueueInterface $moduleQueue): void
    {
        $em = $this->getEntityManager();

        $em->remove($moduleQueue);
        $em->flush();
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

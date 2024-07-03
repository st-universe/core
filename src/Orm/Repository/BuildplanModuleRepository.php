<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\BuildplanModuleInterface;

/**
 * @extends EntityRepository<BuildplanModule>
 */
final class BuildplanModuleRepository extends EntityRepository implements BuildplanModuleRepositoryInterface
{
    #[Override]
    public function getByBuildplan(int $buildplanId): array
    {
        return $this->findBy(
            ['buildplan_id' => $buildplanId],
            ['module_type' => 'asc']
        );
    }

    #[Override]
    public function getByBuildplanAndModuleType(int $buildplanId, int $moduleType): array
    {
        return $this->findBy([
            'buildplan_id' => $buildplanId,
            'module_type' => $moduleType
        ]);
    }

    #[Override]
    public function prototype(): BuildplanModuleInterface
    {
        return new BuildplanModule();
    }

    #[Override]
    public function save(BuildplanModuleInterface $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }

    #[Override]
    public function delete(BuildplanModuleInterface $obj): void
    {
        $em = $this->getEntityManager();

        $em->remove($obj);
        $em->flush();
    }

    #[Override]
    public function truncateByBuildplan(int $buildplanId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s bm WHERE bm.buildplan_id = :buildplanId',
                    BuildplanModule::class
                )
            )
            ->setParameters([
                'buildplanId' => $buildplanId
            ])
            ->execute();
    }
}

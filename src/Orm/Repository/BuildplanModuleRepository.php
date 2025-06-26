<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\BuildplanModule;

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
    public function prototype(): BuildplanModule
    {
        return new BuildplanModule();
    }

    #[Override]
    public function save(BuildplanModule $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }

    #[Override]
    public function delete(BuildplanModule $obj): void
    {
        $em = $this->getEntityManager();

        $em->remove($obj);
        $em->flush(); //TODO really neccessary?
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

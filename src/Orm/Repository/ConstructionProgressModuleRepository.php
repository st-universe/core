<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ConstructionProgressModule;
use Stu\Orm\Entity\ConstructionProgressModuleInterface;

/**
 * @extends EntityRepository<ConstructionProgressModule>
 */
final class ConstructionProgressModuleRepository extends EntityRepository implements ConstructionProgressModuleRepositoryInterface
{
    public function prototype(): ConstructionProgressModuleInterface
    {
        return new ConstructionProgressModule();
    }

    public function save(ConstructionProgressModuleInterface $constructionProgressModule): void
    {
        $em = $this->getEntityManager();

        $em->persist($constructionProgressModule);
    }

    public function delete(ConstructionProgressModuleInterface $constructionProgressModule): void
    {
        $em = $this->getEntityManager();

        $em->remove($constructionProgressModule);
        $em->flush();
    }

    public function truncateByProgress(int $progressId): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'delete from %s t where t.progress_id = :progressId',
                ConstructionProgressModule::class
            )
        );
        $q->setParameter('progressId', $progressId);
        $q->execute();

        $em = $this->getEntityManager();
        $em->flush();
    }
}

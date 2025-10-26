<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ConstructionProgressModule;

/**
 * @extends EntityRepository<ConstructionProgressModule>
 */
final class ConstructionProgressModuleRepository extends EntityRepository implements ConstructionProgressModuleRepositoryInterface
{
    #[\Override]
    public function prototype(): ConstructionProgressModule
    {
        return new ConstructionProgressModule();
    }

    #[\Override]
    public function save(ConstructionProgressModule $constructionProgressModule): void
    {
        $em = $this->getEntityManager();

        $em->persist($constructionProgressModule);
    }

    #[\Override]
    public function delete(ConstructionProgressModule $constructionProgressModule): void
    {
        $em = $this->getEntityManager();

        $em->remove($constructionProgressModule);
        $em->flush();
    }

    #[\Override]
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

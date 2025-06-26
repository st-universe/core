<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ConstructionProgress;

/**
 * @extends EntityRepository<ConstructionProgress>
 */
final class ConstructionProgressRepository extends EntityRepository implements ConstructionProgressRepositoryInterface
{
    #[Override]
    public function prototype(): ConstructionProgress
    {
        return new ConstructionProgress();
    }

    #[Override]
    public function save(ConstructionProgress $constructionProgress): void
    {
        $em = $this->getEntityManager();

        $em->persist($constructionProgress);
    }

    #[Override]
    public function delete(ConstructionProgress $constructionProgress): void
    {
        $em = $this->getEntityManager();

        $em->remove($constructionProgress);
        $em->flush();
    }
}

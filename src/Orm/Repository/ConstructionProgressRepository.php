<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\ConstructionProgressInterface;

/**
 * @extends EntityRepository<ConstructionProgress>
 */
final class ConstructionProgressRepository extends EntityRepository implements ConstructionProgressRepositoryInterface
{
    #[Override]
    public function getByShip(int $shipId): ?ConstructionProgressInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT cp FROM %s cp
                    WHERE cp.ship_id = :shipId',
                    ConstructionProgress::class
                )
            )
            ->setParameters(['shipId' => $shipId])
            ->getOneOrNullResult();
    }

    #[Override]
    public function prototype(): ConstructionProgressInterface
    {
        return new ConstructionProgress();
    }

    #[Override]
    public function save(ConstructionProgressInterface $constructionProgress): void
    {
        $em = $this->getEntityManager();

        $em->persist($constructionProgress);
    }

    #[Override]
    public function delete(ConstructionProgressInterface $constructionProgress): void
    {
        $em = $this->getEntityManager();

        $em->remove($constructionProgress);
        $em->flush();
    }
}

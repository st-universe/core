<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\StationInterface;

/**
 * @extends EntityRepository<ConstructionProgress>
 */
final class ConstructionProgressRepository extends EntityRepository implements ConstructionProgressRepositoryInterface
{
    #[Override]
    public function getByStation(StationInterface $station): ?ConstructionProgressInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT cp FROM %s cp
                    WHERE cp.station = :station',
                    ConstructionProgress::class
                )
            )
            ->setParameter('station', $station)
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

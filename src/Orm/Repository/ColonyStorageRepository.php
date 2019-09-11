<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonyStorage;
use Stu\Orm\Entity\ColonyStorageInterface;
use Stu\Orm\Entity\Commodity;

final class ColonyStorageRepository extends EntityRepository implements ColonyStorageRepositoryInterface
{

    public function prototype(): ColonyStorageInterface
    {
        return new ColonyStorage();
    }

    public function save(ColonyStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush($post);
    }

    public function delete(ColonyStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush($post);
    }

    public function getByColony(int $colonyId, int $viewable = 1): array
    {
        /** @noinspection SyntaxError */
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT cs FROM %s cs INDEX BY cs.goods_id LEFT JOIN %s g WITH g.id = cs.goods_id
                        WHERE cs.colonies_id = :colonyId ORDER BY g.sort',
                    ColonyStorage::class,
                    Commodity::class
                )
            )
            ->setParameters([
                'colonyId' => $colonyId,
            ])
            ->getResult();
    }

    public function truncateByColony(int $colonyId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s cs WHERE tc.colonies_id = :colonyId',
                    ColonyStorage::class
                )
            )
            ->setParameter('colonyId', $colonyId)
            ->execute();
    }
}
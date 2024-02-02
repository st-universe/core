<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use RuntimeException;
use Stu\Component\Map\MapEnum;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\UserLayer;

/**
 * @extends EntityRepository<Layer>
 */
final class LayerRepository extends EntityRepository implements LayerRepositoryInterface
{
    public function prototype(): LayerInterface
    {
        return new Layer();
    }

    public function save(LayerInterface $layer): void
    {
        $em = $this->getEntityManager();

        $em->persist($layer);
    }

    public function delete(LayerInterface $layer): void
    {
        $em = $this->getEntityManager();

        $em->remove($layer);
    }

    public function findAllIndexed(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT l
                    FROM %s l INDEX BY l.id
                    ORDER BY l.id ASC',
                    Layer::class
                )
            )
            ->getResult();
    }

    public function getKnownByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT l
                    FROM %s l INDEX BY l.id
                    JOIN %s ul
                    WITH ul.layer_id = l.id
                    WHERE ul.user_id = :userId
                    ORDER BY l.id ASC',
                    Layer::class,
                    UserLayer::class
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    public function getDefaultLayer(): LayerInterface
    {
        $layer = $this->find(MapEnum::DEFAULT_LAYER);

        if ($layer === null) {
            throw new RuntimeException('this should not happen');
        }

        return $layer;
    }
}

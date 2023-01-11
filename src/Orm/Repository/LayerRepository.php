<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Layer;

final class LayerRepository extends EntityRepository implements LayerRepositoryInterface
{
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
}

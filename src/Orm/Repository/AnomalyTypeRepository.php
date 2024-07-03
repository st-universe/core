<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\AnomalyType;
use Stu\Orm\Entity\AnomalyTypeInterface;

/**
 * @extends EntityRepository<AnomalyType>
 */
final class AnomalyTypeRepository extends EntityRepository implements AnomalyTypeRepositoryInterface
{
    #[Override]
    public function prototype(): AnomalyTypeInterface
    {
        return new AnomalyType();
    }

    #[Override]
    public function save(AnomalyTypeInterface $anomalytype): void
    {
        $em = $this->getEntityManager();

        $em->persist($anomalytype);
    }
}

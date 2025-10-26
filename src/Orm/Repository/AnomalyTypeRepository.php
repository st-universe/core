<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AnomalyType;

/**
 * @extends EntityRepository<AnomalyType>
 */
final class AnomalyTypeRepository extends EntityRepository implements AnomalyTypeRepositoryInterface
{
    #[\Override]
    public function prototype(): AnomalyType
    {
        return new AnomalyType();
    }

    #[\Override]
    public function save(AnomalyType $anomalytype): void
    {
        $em = $this->getEntityManager();

        $em->persist($anomalytype);
    }
}

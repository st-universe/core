<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PirateSetup;

/**
 * @extends EntityRepository<PirateSetup>
 *
 * @method PirateSetup[] findAll()
 */
final class PirateSetupRepository extends EntityRepository implements PirateSetupRepositoryInterface
{
    public function getAllOrderedByName(): array
    {
        return $this->createQueryBuilder('ps')
            ->orderBy('ps.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

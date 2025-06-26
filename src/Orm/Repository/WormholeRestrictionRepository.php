<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\WormholeRestriction;

/**
 * @extends EntityRepository<WormholeRestriction>
 */
final class WormholeRestrictionRepository extends EntityRepository implements WormholeRestrictionRepositoryInterface
{
    #[Override]
    public function save(WormholeRestriction $restriction): void
    {
        $em = $this->getEntityManager();
        $em->persist($restriction);
    }
}

<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\WormholeRestriction;
use Stu\Orm\Entity\WormholeRestrictionInterface;

/**
 * @extends EntityRepository<WormholeRestriction>
 */
final class WormholeRestrictionRepository extends EntityRepository implements WormholeRestrictionRepositoryInterface
{
    #[Override]
    public function save(WormholeRestrictionInterface $restriction): void
    {
        $em = $this->getEntityManager();
        $em->persist($restriction);
    }
}

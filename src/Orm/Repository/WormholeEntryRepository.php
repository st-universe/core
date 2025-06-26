<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\WormholeEntry;

/**
 * @extends EntityRepository<WormholeEntry>
 */
final class WormholeEntryRepository extends EntityRepository implements WormholeEntryRepositoryInterface
{
    #[Override]
    public function save(WormholeEntry $entry): void
    {
        $em = $this->getEntityManager();
        $em->persist($entry);
    }
}

<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\WormholeEntry;
use Stu\Orm\Entity\WormholeEntryInterface;

/**
 * @extends EntityRepository<WormholeEntry>
 */
final class WormholeEntryRepository extends EntityRepository implements WormholeEntryRepositoryInterface
{
    public function save(WormholeEntryInterface $entry): void
    {
        $em = $this->getEntityManager();
        $em->persist($entry);
    }
}

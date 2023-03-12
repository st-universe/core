<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\WormholeEntry;
use Stu\Orm\Entity\WormholeEntryInterface;

/**
 * @extends ObjectRepository<WormholeEntry>
 *
 * @method null|WormholeEntryInterface find(integer $id)
 */
interface WormholeEntryRepositoryInterface extends ObjectRepository
{
    public function save(WormholeEntryInterface $entry): void;
}

<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\WormholeEntry;

/**
 * @extends ObjectRepository<WormholeEntry>
 *
 * @method null|WormholeEntry find(integer $id)
 */
interface WormholeEntryRepositoryInterface extends ObjectRepository
{
    public function save(WormholeEntry $entry): void;
}

<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\WormholeRestriction;

/**
 * @extends ObjectRepository<WormholeRestriction>
 *
 * @method null|WormholeRestriction find(integer $id)
 */
interface WormholeRestrictionRepositoryInterface extends ObjectRepository
{
    public function save(WormholeRestriction $restriction): void;
}

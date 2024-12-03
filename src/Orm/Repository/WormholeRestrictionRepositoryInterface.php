<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\WormholeRestriction;
use Stu\Orm\Entity\WormholeRestrictionInterface;

/**
 * @extends ObjectRepository<WormholeRestriction>
 *
 * @method null|WormholeRestrictionInterface find(integer $id)
 */
interface WormholeRestrictionRepositoryInterface extends ObjectRepository
{
    public function save(WormholeRestrictionInterface $restriction): void;
}

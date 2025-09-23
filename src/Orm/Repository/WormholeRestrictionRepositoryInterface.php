<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Ship\Wormhole\WormholeEntryTypeEnum;
use Stu\Orm\Entity\WormholeRestriction;
use Stu\Orm\Entity\WormholeEntry;

/**
 * @extends ObjectRepository<WormholeRestriction>
 *
 * @method null|WormholeRestriction find(integer $id)
 */
interface WormholeRestrictionRepositoryInterface extends ObjectRepository
{
    public function prototype(): WormholeRestriction;

    public function save(WormholeRestriction $restriction): void;

    public function delete(WormholeRestriction $restriction): void;

    public function existsForTargetAndTypeAndEntry(int $targetId, ?WormholeEntryTypeEnum $privilegeType, WormholeEntry $wormholeEntry): bool;

    public function truncateByTypeAndTarget(?WormholeEntryTypeEnum $type, int $targetId): void;
}

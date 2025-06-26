<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\Station;

/**
 * @extends ObjectRepository<DockingPrivilege>
 *
 * @method null|DockingPrivilege find(integer $id)
 */
interface DockingPrivilegeRepositoryInterface extends ObjectRepository
{
    public function prototype(): DockingPrivilege;

    public function save(DockingPrivilege $post): void;

    public function delete(DockingPrivilege $post): void;

    public function existsForTargetAndTypeAndShip(int $targetId, DockTypeEnum $privilegeType, Station $station): bool;

    public function truncateByTypeAndTarget(DockTypeEnum $type, int $targetId): void;
}

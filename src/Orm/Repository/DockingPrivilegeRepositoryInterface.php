<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\DockingPrivilegeInterface;

/**
 * @extends ObjectRepository<DockingPrivilege>
 *
 * @method null|DockingPrivilegeInterface find(integer $id)
 */
interface DockingPrivilegeRepositoryInterface extends ObjectRepository
{
    public function prototype(): DockingPrivilegeInterface;

    public function save(DockingPrivilegeInterface $post): void;

    public function delete(DockingPrivilegeInterface $post): void;

    public function existsForTargetAndTypeAndShip(int $targetId, DockTypeEnum $privilegeType, int $shipId): bool;

    /**
     * @return list<DockingPrivilegeInterface>
     */
    public function getByStation(int $stationId): array;

    public function truncateByTypeAndTarget(DockTypeEnum $type, int $targetId): void;
}

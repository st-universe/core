<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
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

    public function existsForTargetAndTypeAndShip(int $targetId, int $privilegeType, int $shipId): bool;

    /**
     * @return list<DockingPrivilegeInterface>
     */
    public function getByShip(int $shipId): array;

    public function truncateByTypeAndTarget(int $typeId, int $targetId): void;
}

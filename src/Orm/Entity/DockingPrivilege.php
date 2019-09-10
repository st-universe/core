<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\FactionRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\DockingPrivilegeRepository")
 * @Table(
 *     name="stu_dockingrights",
 *     indexes={
 *         @Index(name="ship_idx", columns={"ships_id"})
 *     }
 * )
 **/
class DockingPrivilege implements DockingPrivilegeInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $ships_id = 0;

    /** @Column(type="integer") * */
    private $target = 0;

    /** @Column(type="smallint") * */
    private $privilege_type = 0;

    /** @Column(type="smallint") * */
    private $privilege_mode = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipId(): int
    {
        return $this->ships_id;
    }

    public function setShipId(int $shipId): DockingPrivilegeInterface
    {
        $this->ships_id = $shipId;
        return $this;
    }

    public function getTargetId(): int
    {
        return $this->target;
    }

    public function setTargetId(int $targetId): DockingPrivilegeInterface
    {
        $this->target = $targetId;
        return $this;
    }

    public function getPrivilegeType(): int
    {
        return $this->privilege_type;
    }

    public function setPrivilegeType(int $privilegeType): DockingPrivilegeInterface
    {
        $this->privilege_type = $privilegeType;
        return $this;
    }

    public function getPrivilegeMode(): int
    {
        return $this->privilege_mode;
    }

    public function setPrivilegeMode(int $privilegeMode): DockingPrivilegeInterface
    {
        $this->privilege_mode = $privilegeMode;
        return $this;
    }

    public function getPrivilegeModeString(): string
    {
        // @todo refactor
        if ($this->getPrivilegeMode() == DOCK_PRIVILEGE_MODE_ALLOW) {
            return _('Erlaubt');
        }
        return _('Verboten');
    }

    public function isDockingAllowed(): bool
    {
        return $this->getPrivilegeMode() == DOCK_PRIVILEGE_MODE_ALLOW;
    }

    public function getTargetName(): string
    {
        // @todo refactor
        global $container;
        switch ($this->getPrivilegeType()) {
            case DOCK_PRIVILEGE_USER:
                return ResourceCache()->getObject('user', $this->getTargetId())->getName();
            case DOCK_PRIVILEGE_ALLIANCE:
                return ResourceCache()->getObject('alliance', $this->getTargetId())->getName();
            case DOCK_PRIVILEGE_FACTION:
                return $container->get(FactionRepositoryInterface::class)->find((int)$this->getTargetId())->getName();

        }
        return ResourceCache()->getObject('ship', $this->getTargetId())->getName();
    }
}

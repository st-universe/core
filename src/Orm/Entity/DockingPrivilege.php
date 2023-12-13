<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_dockingrights')]
#[Index(name: 'dockingrights_ship_idx', columns: ['ships_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\DockingPrivilegeRepository')]
class DockingPrivilege implements DockingPrivilegeInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $ships_id = 0;

    #[Column(type: 'integer')]
    private int $target = 0; //TODO create refs to user, ally, ship and faction entities and make cascade delete
    #[Column(type: 'smallint')]
    private int $privilege_type = 0;

    #[Column(type: 'smallint')]
    private int $privilege_mode = 0;

    #[ManyToOne(targetEntity: 'Ship', inversedBy: 'dockingPrivileges')]
    #[JoinColumn(name: 'ships_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $ship;

    public function getId(): int
    {
        return $this->id;
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

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): DockingPrivilegeInterface
    {
        $this->ship = $ship;
        return $this;
    }
}

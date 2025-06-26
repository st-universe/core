<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Orm\Repository\DockingPrivilegeRepository;

#[Table(name: 'stu_dockingrights')]
#[Entity(repositoryClass: DockingPrivilegeRepository::class)]
class DockingPrivilege
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $target = 0; //TODO create refs to user, ally, ship and faction entities and make cascade delete

    #[Column(type: 'smallint', enumType: DockTypeEnum::class)]
    private DockTypeEnum $privilege_type = DockTypeEnum::ALLIANCE;

    #[Column(type: 'smallint', enumType: DockModeEnum::class)]
    private DockModeEnum $privilege_mode = DockModeEnum::ALLOW;

    #[ManyToOne(targetEntity: Station::class, inversedBy: 'dockingPrivileges')]
    #[JoinColumn(name: 'station_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Station $station;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTargetId(): int
    {
        return $this->target;
    }

    public function setTargetId(int $targetId): DockingPrivilege
    {
        $this->target = $targetId;
        return $this;
    }

    public function getPrivilegeType(): DockTypeEnum
    {
        return $this->privilege_type;
    }

    public function setPrivilegeType(DockTypeEnum $privilegeType): DockingPrivilege
    {
        $this->privilege_type = $privilegeType;
        return $this;
    }

    public function getPrivilegeMode(): DockModeEnum
    {
        return $this->privilege_mode;
    }

    public function setPrivilegeMode(DockModeEnum $privilegeMode): DockingPrivilege
    {
        $this->privilege_mode = $privilegeMode;
        return $this;
    }

    public function getStation(): Station
    {
        return $this->station;
    }

    public function setStation(Station $station): DockingPrivilege
    {
        $this->station = $station;
        return $this;
    }
}

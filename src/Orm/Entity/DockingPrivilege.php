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
use Override;
use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Orm\Repository\DockingPrivilegeRepository;

#[Table(name: 'stu_dockingrights')]
#[Entity(repositoryClass: DockingPrivilegeRepository::class)]
class DockingPrivilege implements DockingPrivilegeInterface
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

    #[ManyToOne(targetEntity: 'Station', inversedBy: 'dockingPrivileges')]
    #[JoinColumn(name: 'station_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private StationInterface $station;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getTargetId(): int
    {
        return $this->target;
    }

    #[Override]
    public function setTargetId(int $targetId): DockingPrivilegeInterface
    {
        $this->target = $targetId;
        return $this;
    }

    #[Override]
    public function getPrivilegeType(): DockTypeEnum
    {
        return $this->privilege_type;
    }

    #[Override]
    public function setPrivilegeType(DockTypeEnum $privilegeType): DockingPrivilegeInterface
    {
        $this->privilege_type = $privilegeType;
        return $this;
    }

    #[Override]
    public function getPrivilegeMode(): DockModeEnum
    {
        return $this->privilege_mode;
    }

    #[Override]
    public function setPrivilegeMode(DockModeEnum $privilegeMode): DockingPrivilegeInterface
    {
        $this->privilege_mode = $privilegeMode;
        return $this;
    }

    #[Override]
    public function getStation(): StationInterface
    {
        return $this->station;
    }

    #[Override]
    public function setStation(StationInterface $station): DockingPrivilegeInterface
    {
        $this->station = $station;
        return $this;
    }
}

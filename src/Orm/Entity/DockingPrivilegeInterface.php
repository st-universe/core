<?php

namespace Stu\Orm\Entity;

use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;

interface DockingPrivilegeInterface
{
    public function getId(): int;

    public function getTargetId(): int;

    public function setTargetId(int $targetId): DockingPrivilegeInterface;

    public function getPrivilegeType(): DockTypeEnum;

    public function setPrivilegeType(DockTypeEnum $privilegeType): DockingPrivilegeInterface;

    public function getPrivilegeMode(): DockModeEnum;

    public function setPrivilegeMode(DockModeEnum $privilegeMode): DockingPrivilegeInterface;

    public function getStation(): StationInterface;

    public function setStation(StationInterface $station): DockingPrivilegeInterface;
}

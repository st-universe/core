<?php

namespace Stu\Orm\Entity;

use Stu\Component\Crew\CrewPositionEnum;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;

interface CrewAssignmentInterface
{
    public function getPosition(): ?CrewPositionEnum;

    public function setPosition(?CrewPositionEnum $position): CrewAssignmentInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): CrewAssignmentInterface;

    public function getRepairTask(): ?RepairTaskInterface;

    public function setRepairTask(?RepairTaskInterface $repairTask): CrewAssignmentInterface;

    public function getCrew(): CrewInterface;

    public function setCrew(CrewInterface $crew): CrewAssignmentInterface;

    public function getSpacecraft(): ?SpacecraftInterface;

    public function setSpacecraft(?SpacecraftInterface $spacecraft): CrewAssignmentInterface;

    public function getColony(): ?ColonyInterface;

    public function setColony(?ColonyInterface $colony): CrewAssignmentInterface;

    public function getTradepost(): ?TradePostInterface;

    public function setTradepost(?TradePostInterface $tradepost): CrewAssignmentInterface;

    public function getFightCapability(): int;

    public function clearAssignment(): CrewAssignmentInterface;

    public function assign(EntityWithCrewAssignmentsInterface $target): CrewAssignmentInterface;
}

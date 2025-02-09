<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Crew\CrewPositionEnum;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Orm\Repository\CrewAssignmentRepository;

#[Table(name: 'stu_crew_assign')]
#[Entity(repositoryClass: CrewAssignmentRepository::class)]
class CrewAssignment implements CrewAssignmentInterface
{
    #[Id]
    #[OneToOne(targetEntity: 'Crew')]
    #[JoinColumn(name: 'crew_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CrewInterface $crew;

    #[Column(type: 'smallint', enumType: CrewPositionEnum::class, nullable: true)]
    private ?CrewPositionEnum $position = null;

    #[ManyToOne(targetEntity: 'Spacecraft')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?SpacecraftInterface $spacecraft = null;

    #[ManyToOne(targetEntity: 'Colony')]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ColonyInterface $colony = null;

    #[ManyToOne(targetEntity: 'TradePost')]
    #[JoinColumn(name: 'tradepost_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TradePostInterface $tradepost = null;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'RepairTask')]
    #[JoinColumn(name: 'repair_task_id', referencedColumnName: 'id')]
    private ?RepairTaskInterface $repairTask = null;

    #[Override]
    public function getPosition(): ?CrewPositionEnum
    {
        return $this->position ?? CrewPositionEnum::CREWMAN;
    }

    #[Override]
    public function setPosition(?CrewPositionEnum $position): CrewAssignmentInterface
    {
        $this->position = $position;

        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): CrewAssignmentInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getRepairTask(): ?RepairTaskInterface
    {
        return $this->repairTask;
    }

    #[Override]
    public function setRepairTask(?RepairTaskInterface $repairTask): CrewAssignmentInterface
    {
        $this->repairTask = $repairTask;
        return $this;
    }

    #[Override]
    public function getCrew(): CrewInterface
    {
        return $this->crew;
    }

    #[Override]
    public function setCrew(CrewInterface $crew): CrewAssignmentInterface
    {
        $this->crew = $crew;

        return $this;
    }

    #[Override]
    public function getSpacecraft(): ?SpacecraftInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function setSpacecraft(?SpacecraftInterface $spacecraft): CrewAssignmentInterface
    {
        $this->spacecraft = $spacecraft;
        return $this;
    }

    #[Override]
    public function getColony(): ?ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function setColony(?ColonyInterface $colony): CrewAssignmentInterface
    {
        $this->colony = $colony;

        return $this;
    }

    #[Override]
    public function getTradepost(): ?TradePostInterface
    {
        return $this->tradepost;
    }

    #[Override]
    public function setTradepost(?TradePostInterface $tradepost): CrewAssignmentInterface
    {
        $this->tradepost = $tradepost;

        return $this;
    }

    #[Override]
    public function getFightCapability(): int
    {
        $position = $this->getPosition() ?? CrewPositionEnum::CREWMAN;

        return $position->getFightCapability();
    }

    #[Override]
    public function clearAssignment(): CrewAssignmentInterface
    {
        if ($this->spacecraft !== null) {
            $this->spacecraft->getCrewAssignments()->removeElement($this);
        }
        if ($this->colony !== null) {
            $this->colony->getCrewAssignments()->removeElement($this);
        }
        if ($this->tradepost !== null) {
            $this->tradepost->getCrewAssignments()->removeElement($this);
        }

        $this->spacecraft = null;
        $this->colony = null;
        $this->tradepost = null;

        return $this;
    }

    #[Override]
    public function assign(EntityWithCrewAssignmentsInterface $target): CrewAssignmentInterface
    {
        if ($target instanceof ColonyInterface) {
            $this->setColony($target);
        }
        if ($target instanceof SpacecraftInterface) {
            $this->setSpacecraft($target);
        }
        if ($target instanceof TradePostInterface) {
            $this->setTradepost($target);
        }

        return $this;
    }
}

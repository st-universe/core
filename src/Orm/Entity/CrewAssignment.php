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
use Doctrine\ORM\Mapping\UniqueConstraint;
use Override;
use Stu\Component\Crew\CrewEnum;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Orm\Repository\CrewAssignmentRepository;

#[Table(name: 'stu_crew_assign')]
#[UniqueConstraint(name: 'crew_assign_crew_idx', columns: ['crew_id'])]
#[Entity(repositoryClass: CrewAssignmentRepository::class)]
class CrewAssignment implements CrewAssignmentInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $spacecraft_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $colony_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $tradepost_id = null;

    #[Column(type: 'integer')]
    private int $crew_id = 0;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $slot = null;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $repair_task_id = null;

    #[ManyToOne(targetEntity: 'Crew')]
    #[JoinColumn(name: 'crew_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CrewInterface $crew;

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
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'RepairTask')]
    #[JoinColumn(name: 'repair_task_id', referencedColumnName: 'id')]
    private ?RepairTaskInterface $repairTask = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getCrewId(): int
    {
        return $this->crew_id;
    }

    #[Override]
    public function getSlot(): ?int
    {
        return $this->slot;
    }

    #[Override]
    public function setSlot(?int $slot): CrewAssignmentInterface
    {
        $this->slot = $slot;

        return $this;
    }

    #[Override]
    public function getPosition(): string
    {
        return CrewEnum::getDescription($this->getSlot());
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
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
        $this->spacecraft_id = null;
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

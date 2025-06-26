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
use Stu\Component\Crew\CrewEnum;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Orm\Repository\CrewAssignmentRepository;

#[Table(name: 'stu_crew_assign')]
#[Entity(repositoryClass: CrewAssignmentRepository::class)]
class CrewAssignment
{
    #[Id]
    #[OneToOne(targetEntity: Crew::class)]
    #[JoinColumn(name: 'crew_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Crew $crew;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $slot = null;

    #[ManyToOne(targetEntity: Spacecraft::class)]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Spacecraft $spacecraft = null;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Colony $colony = null;

    #[ManyToOne(targetEntity: TradePost::class)]
    #[JoinColumn(name: 'tradepost_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TradePost $tradepost = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ManyToOne(targetEntity: RepairTask::class)]
    #[JoinColumn(name: 'repair_task_id', referencedColumnName: 'id')]
    private ?RepairTask $repairTask = null;

    public function getSlot(): ?int
    {
        return $this->slot;
    }

    public function setSlot(?int $slot): CrewAssignment
    {
        $this->slot = $slot;

        return $this;
    }

    public function getPosition(): string
    {
        return CrewEnum::getDescription($this->getSlot());
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): CrewAssignment
    {
        $this->user = $user;
        return $this;
    }

    public function getRepairTask(): ?RepairTask
    {
        return $this->repairTask;
    }

    public function setRepairTask(?RepairTask $repairTask): CrewAssignment
    {
        $this->repairTask = $repairTask;
        return $this;
    }

    public function getCrew(): Crew
    {
        return $this->crew;
    }

    public function setCrew(Crew $crew): CrewAssignment
    {
        $this->crew = $crew;

        return $this;
    }

    public function getSpacecraft(): ?Spacecraft
    {
        return $this->spacecraft;
    }

    public function setSpacecraft(?Spacecraft $spacecraft): CrewAssignment
    {
        $this->spacecraft = $spacecraft;
        return $this;
    }

    public function getColony(): ?Colony
    {
        return $this->colony;
    }

    public function setColony(?Colony $colony): CrewAssignment
    {
        $this->colony = $colony;

        return $this;
    }

    public function getTradepost(): ?TradePost
    {
        return $this->tradepost;
    }

    public function setTradepost(?TradePost $tradepost): CrewAssignment
    {
        $this->tradepost = $tradepost;

        return $this;
    }

    public function clearAssignment(): CrewAssignment
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

    public function assign(EntityWithCrewAssignmentsInterface $target): CrewAssignment
    {
        if ($target instanceof Colony) {
            $this->setColony($target);
        }
        if ($target instanceof Spacecraft) {
            $this->setSpacecraft($target);
        }
        if ($target instanceof TradePost) {
            $this->setTradepost($target);
        }

        return $this;
    }
}

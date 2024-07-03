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
use Doctrine\ORM\Mapping\UniqueConstraint;
use Override;
use Stu\Component\Crew\CrewEnum;
use Stu\Orm\Repository\ShipCrewRepository;

//TODO RENAME to CrewAssignment, indices, repo and stuff
#[Table(name: 'stu_crew_assign')]
#[Index(name: 'ship_crew_colony_idx', columns: ['colony_id'])]
#[Index(name: 'ship_crew_ship_idx', columns: ['ship_id'])]
#[Index(name: 'ship_crew_tradepost_idx', columns: ['tradepost_id'])]
#[Index(name: 'ship_crew_user_idx', columns: ['user_id'])]
#[UniqueConstraint(name: 'ship_crew_crew_idx', columns: ['crew_id'])]
#[Entity(repositoryClass: ShipCrewRepository::class)]
class ShipCrew implements ShipCrewInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $ship_id = 0;

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

    #[ManyToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ShipInterface $ship = null;

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
    public function setCrewId(int $crewId): ShipCrewInterface
    {
        $this->crew_id = $crewId;

        return $this;
    }

    #[Override]
    public function getShipId(): int
    {
        return $this->ship_id;
    }

    #[Override]
    public function setShipId(int $shipId): ShipCrewInterface
    {
        $this->ship_id = $shipId;

        return $this;
    }

    #[Override]
    public function getSlot(): ?int
    {
        return $this->slot;
    }

    #[Override]
    public function setSlot(?int $slot): ShipCrewInterface
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
    public function setUser(UserInterface $user): ShipCrewInterface
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
    public function setRepairTask(?RepairTaskInterface $repairTask): ShipCrewInterface
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
    public function setCrew(CrewInterface $crew): ShipCrewInterface
    {
        $this->crew = $crew;

        return $this;
    }

    #[Override]
    public function getShip(): ?ShipInterface
    {
        return $this->ship;
    }

    #[Override]
    public function setShip(?ShipInterface $ship): ShipCrewInterface
    {
        $this->ship = $ship;
        return $this;
    }

    #[Override]
    public function getColony(): ?ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function setColony(?ColonyInterface $colony): ShipCrewInterface
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
    public function setTradepost(?TradePostInterface $tradepost): ShipCrewInterface
    {
        $this->tradepost = $tradepost;

        return $this;
    }

    #[Override]
    public function isForeigner(): bool
    {
        return $this->getShip()->getUser() !== $this->getCrew()->getUser();
    }
}

<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\FleetRepository;

#[Table(name: 'stu_fleets')]
#[Index(name: 'fleet_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: FleetRepository::class)]
class Fleet
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string', length: 200)]
    private string $name = '';

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $ships_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $defended_colony_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $blocked_colony_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $sort = null;

    #[Column(type: 'boolean')]
    private bool $is_fixed = false;

    private string $hiddenStyle;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    /**
     * @var ArrayCollection<int, Ship>
     */
    #[OneToMany(targetEntity: Ship::class, mappedBy: 'fleet', indexBy: 'id')]
    #[OrderBy(['is_fleet_leader' => 'DESC', 'name' => 'ASC'])]
    private Collection $shiplist;

    #[OneToOne(targetEntity: Ship::class)]
    #[JoinColumn(name: 'ships_id', nullable: false, referencedColumnName: 'id')]
    private Ship $fleetLeader;

    #[ManyToOne(targetEntity: Colony::class, inversedBy: 'defenders')]
    #[JoinColumn(name: 'defended_colony_id', referencedColumnName: 'id')]
    private ?Colony $defendedColony = null;

    #[ManyToOne(targetEntity: Colony::class, inversedBy: 'blockers')]
    #[JoinColumn(name: 'blocked_colony_id', referencedColumnName: 'id')]
    private ?Colony $blockedColony = null;

    public function __construct()
    {
        $this->shiplist = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Fleet
    {
        $this->name = $name;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @return Collection<int, Ship>
     */
    public function getShips(): Collection
    {
        return $this->shiplist;
    }

    public function getShipCount(): int
    {
        return $this->getShips()->count();
    }

    public function getLeadShip(): Ship
    {
        return $this->fleetLeader;
    }

    public function setLeadShip(Ship $ship): Fleet
    {
        $this->fleetLeader = $ship;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Fleet
    {
        $this->user = $user;
        return $this;
    }

    public function getDefendedColony(): ?Colony
    {
        return $this->defendedColony;
    }

    public function setDefendedColony(?Colony $defendedColony): Fleet
    {
        $this->defendedColony = $defendedColony;
        return $this;
    }

    public function getBlockedColony(): ?Colony
    {
        return $this->blockedColony;
    }

    public function setBlockedColony(?Colony $blockedColony): Fleet
    {
        $this->blockedColony = $blockedColony;
        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): Fleet
    {
        $this->sort = $sort;

        return $this;
    }

    public function isFleetFixed(): bool
    {
        return $this->is_fixed;
    }

    public function setIsFleetFixed(bool $isFixed): Fleet
    {
        $this->is_fixed = $isFixed;
        return $this;
    }

    public function getCrewSum(): int
    {
        return array_reduce(
            $this->shiplist->toArray(),
            fn(int $sum, Ship $ship): int => $sum + ($ship->getCondition()->isDestroyed() ? 0 : $ship->getBuildplan()->getCrew()),
            0
        );
    }

    public function getHiddenStyle(): string
    {
        return $this->hiddenStyle;
    }

    public function setHiddenStyle(string $hiddenStyle): Fleet
    {
        $this->hiddenStyle = $hiddenStyle;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}

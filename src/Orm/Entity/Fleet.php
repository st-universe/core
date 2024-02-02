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

#[Table(name: 'stu_fleets')]
#[Index(name: 'fleet_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\FleetRepository')]
class Fleet implements FleetInterface
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

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    /**
     * @var ArrayCollection<int, ShipInterface>
     */
    #[OneToMany(targetEntity: 'Ship', mappedBy: 'fleet', indexBy: 'id')]
    #[OrderBy(['is_fleet_leader' => 'DESC', 'name' => 'ASC'])]
    private Collection $shiplist;

    #[OneToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'ships_id', referencedColumnName: 'id')]
    private ShipInterface $fleetLeader;

    #[ManyToOne(targetEntity: 'Colony', inversedBy: 'defenders')]
    #[JoinColumn(name: 'defended_colony_id', referencedColumnName: 'id')]
    private ?ColonyInterface $defendedColony = null;

    #[ManyToOne(targetEntity: 'Colony', inversedBy: 'blockers')]
    #[JoinColumn(name: 'blocked_colony_id', referencedColumnName: 'id')]
    private ?ColonyInterface $blockedColony = null;

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

    public function setName(string $name): FleetInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getShips(): Collection
    {
        return $this->shiplist;
    }

    public function getShipCount(): int
    {
        return $this->getShips()->count();
    }

    public function getLeadShip(): ShipInterface
    {
        return $this->fleetLeader;
    }

    public function setLeadShip(ShipInterface $ship): FleetInterface
    {
        $this->fleetLeader = $ship;
        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): FleetInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getDefendedColony(): ?ColonyInterface
    {
        return $this->defendedColony;
    }

    public function setDefendedColony(?ColonyInterface $defendedColony): FleetInterface
    {
        $this->defendedColony = $defendedColony;
        return $this;
    }

    public function getBlockedColony(): ?ColonyInterface
    {
        return $this->blockedColony;
    }

    public function setBlockedColony(?ColonyInterface $blockedColony): FleetInterface
    {
        $this->blockedColony = $blockedColony;
        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): FleetInterface
    {
        $this->sort = $sort;

        return $this;
    }

    public function isFleetFixed(): bool
    {
        return $this->is_fixed;
    }

    public function setIsFleetFixed(bool $isFixed): FleetInterface
    {
        $this->is_fixed = $isFixed;
        return $this;
    }

    public function getCrewSum(): int
    {
        return array_reduce(
            $this->shiplist->toArray(),
            fn (int $sum, ShipInterface $ship): int => $sum + ($ship->isDestroyed() ? 0 : $ship->getBuildplan()->getCrew()),
            0
        );
    }

    public function getHiddenStyle(): string
    {
        return $this->hiddenStyle;
    }

    public function setHiddenStyle(string $hiddenStyle): FleetInterface
    {
        $this->hiddenStyle = $hiddenStyle;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}

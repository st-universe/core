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
use Override;
use Stu\Orm\Repository\FleetRepository;

#[Table(name: 'stu_fleets')]
#[Index(name: 'fleet_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: FleetRepository::class)]
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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): FleetInterface
    {
        $this->name = $name;
        return $this;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getShips(): Collection
    {
        return $this->shiplist;
    }

    #[Override]
    public function getShipCount(): int
    {
        return $this->getShips()->count();
    }

    #[Override]
    public function getLeadShip(): ShipInterface
    {
        return $this->fleetLeader;
    }

    #[Override]
    public function setLeadShip(ShipInterface $ship): FleetInterface
    {
        $this->fleetLeader = $ship;
        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): FleetInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getDefendedColony(): ?ColonyInterface
    {
        return $this->defendedColony;
    }

    #[Override]
    public function setDefendedColony(?ColonyInterface $defendedColony): FleetInterface
    {
        $this->defendedColony = $defendedColony;
        return $this;
    }

    #[Override]
    public function getBlockedColony(): ?ColonyInterface
    {
        return $this->blockedColony;
    }

    #[Override]
    public function setBlockedColony(?ColonyInterface $blockedColony): FleetInterface
    {
        $this->blockedColony = $blockedColony;
        return $this;
    }

    #[Override]
    public function getSort(): ?int
    {
        return $this->sort;
    }

    #[Override]
    public function setSort(?int $sort): FleetInterface
    {
        $this->sort = $sort;

        return $this;
    }

    #[Override]
    public function isFleetFixed(): bool
    {
        return $this->is_fixed;
    }

    #[Override]
    public function setIsFleetFixed(bool $isFixed): FleetInterface
    {
        $this->is_fixed = $isFixed;
        return $this;
    }

    #[Override]
    public function getCrewSum(): int
    {
        return array_reduce(
            $this->shiplist->toArray(),
            fn (int $sum, ShipInterface $ship): int => $sum + ($ship->isDestroyed() ? 0 : $ship->getBuildplan()->getCrew()),
            0
        );
    }

    #[Override]
    public function getHiddenStyle(): string
    {
        return $this->hiddenStyle;
    }

    #[Override]
    public function setHiddenStyle(string $hiddenStyle): FleetInterface
    {
        $this->hiddenStyle = $hiddenStyle;
        return $this;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->getName();
    }
}

<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Lib\SessionInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\FleetRepository")
 * @Table(
 *     name="stu_fleets",
 *     indexes={
 *         @Index(name="fleet_user_idx", columns={"user_id"})
 *     }
 * )
 **/
class Fleet implements FleetInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="string", length=200) */
    private $name = '';

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $ships_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private $defended_colony_id;

    /** @Column(type="integer", nullable=true) * */
    private $blocked_colony_id;

    /** @Column(type="integer", nullable=true) * */
    private $sort;

    /** @Column(type="boolean") */
    private $is_fixed = false;

    private $hiddenStyle;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @OneToMany(targetEntity="Ship", mappedBy="fleet", indexBy="id")
     * @OrderBy({"is_fleet_leader" = "DESC", "name" = "ASC"})
     */
    private $shiplist;

    /**
     * @OneToOne(targetEntity="Ship")
     * @JoinColumn(name="ships_id", referencedColumnName="id")
     */
    private $fleetLeader;

    /**
     * @ManyToOne(targetEntity="Colony", inversedBy="defenders")
     * @JoinColumn(name="defended_colony_id", referencedColumnName="id")
     */
    private $defendedColony;

    /**
     * @ManyToOne(targetEntity="Colony", inversedBy="blockers")
     * @JoinColumn(name="blocked_colony_id", referencedColumnName="id")
     */
    private $blockedColony;

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
            function (int $sum, ShipInterface $ship): int {
                return $sum + ($ship->isDestroyed() ? 0 : $ship->getBuildplan()->getCrew());
            },
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
}

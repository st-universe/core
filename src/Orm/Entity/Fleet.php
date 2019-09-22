<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Ship;
use Stu\Orm\Repository\FleetRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\FleetRepository")
 * @Table(
 *     name="stu_fleets",
 *     indexes={
 *         @Index(name="user_idx", columns={"user_id"})
 *     }
 * )
 **/
class Fleet implements FleetInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="string", length=200) */
    private $name = '';

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $ships_id = 0;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var null|Ship[]
     */
    private $shiplist;

    /**
     * @var null|Ship
     */
    private $fleetLeader;

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

    public function getFleetLeader(): int
    {
        return $this->ships_id;
    }

    public function setFleetLeader(int $leaderShipId): FleetInterface
    {
        $this->ships_id = $leaderShipId;
        return $this;
    }

    public function getShips(): iterable
    {
        if ($this->shiplist === null) {
            $this->shiplist = Ship::getObjectsBy("WHERE fleets_id=" . $this->getId() . " ORDER BY is_base DESC, id LIMIT 200");
        }
        return $this->shiplist;
    }

    public function getShipCount(): int
    {
        return Ship::countInstances("WHERE fleets_id=" . $this->getId());
    }

    public function ownedByCurrentUser(): bool
    {
        return currentUser()->getId() === $this->getUserId();
    }

    public function getLeadShip(): Ship
    {
        return new Ship($this->getFleetLeader());
    }

    public function getAvailableShips(): iterable
    {
        return Ship::getObjectsBy(
        "WHERE user_id=" . currentUser()->getId() . " AND fleets_id=0
            AND ((systems_id=0 AND cx=" . $this->getLeadShip()->getCX() . " AND cy=" . $this->getLeadShip()->getCY() . ") OR
            (systems_id>0 AND sx=" . $this->getLeadShip()->getSX() . " AND sy=" . $this->getLeadShip()->getSY() . " AND
            systems_id=" . $this->getLeadShip()->getSystemsId() . ")) AND id!=" . $this->getLeadShip()->getId() . " AND is_base=0"
        );
    }

    public function autochangeLeader(Ship $obj): void
    {
        // @todo refactor
        global $container;

        $fleetRepo = $container->get(FleetRepositoryInterface::class);

        $ship = Ship::getObjectBy("WHERE fleets_id=" . $this->getId() . " AND id!=" . $obj->getId());
        if (!$ship) {
            $fleetRepo->delete($this);
            $obj->setFleetId(0);
            return;
        }
        $this->setFleetLeader($ship->getId());
        $this->fleetLeader = null;

        $fleetRepo->save($this);
    }

    public function deactivateSystem(int $system): void
    {
        foreach ($this->getShips() as $ship) {
            $ship->deactivateSystem($system);
            $ship->save();
        }
    }

    public function activateSystem(int $system): void
    {
        foreach ($this->getShips() as $ship) {
            $ship->activateSystem($system);
            $ship->save();
        }
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

    public function getPointSum(): int
    {
        return (int)DB()->query(
            'SELECT SUM(c.points) FROM stu_ships as a LEFT JOIN stu_rumps as b ON (b.id=a.rumps_id) LEFT JOIN stu_rumps_categories as c ON (c.id=b.category_id) WHERE a.fleets_id=' . $this->getId(),
            1
        );
    }
}

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
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ConstructionProgressRepository")
 * @Table(
 *     name="stu_construction_progress",
 *     indexes={
 *         @Index(name="construction_progress_ship_idx", columns={"ship_id"})
 *     }
 * )
 **/
class ConstructionProgress implements ConstructionProgressInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $ship_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $remaining_ticks = 0;

    /**
     * @var ArrayCollection<int, ConstructionProgressModuleInterface>
     *
     * @OneToMany(targetEntity="ConstructionProgressModule", mappedBy="progress")
     */
    private Collection $specialModules;

    /**
     *
     * @OneToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ShipInterface $ship;

    public function __construct()
    {
        $this->specialModules = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    private function getShipId(): int
    {
        return $this->ship_id;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): ConstructionProgressInterface
    {
        $this->ship = $ship;

        return $this;
    }

    public function getSpecialModules(): Collection
    {
        return $this->specialModules;
    }

    public function getRemainingTicks(): int
    {
        return $this->remaining_ticks;
    }

    public function setRemainingTicks(int $remainingTicks): ConstructionProgressInterface
    {
        $this->remaining_ticks = $remainingTicks;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('constructionProgress, shipId: %d', $this->getShipId());
    }
}

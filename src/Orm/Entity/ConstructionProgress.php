<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
     */
    private $id;

    /** @Column(type="integer") * */
    private $ship_id = 0;

    /** @Column(type="integer") */
    private $remaining_ticks = 0;

    /**
     * @OneToMany(targetEntity="ConstructionProgressModule", mappedBy="progress")
     */
    private $specialModules;

    /**
     * @OneToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    public function __construct()
    {
        $this->specialModules = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function setShipId(int $shipId): ConstructionProgressInterface
    {
        $this->ship_id = $shipId;

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

    public function __toString()
    {
        return $this->getId !== null ? sprintf('constructionProgressId: %d, shipId: %d', $this->getId(), $this->getShipId()) :
            sprintf('constructionProgressId: null, shipId: %d', $this->getShipId());
    }
}

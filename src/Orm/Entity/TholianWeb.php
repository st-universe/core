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
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TholianWebRepository")
 * @Table(
 *     name="stu_tholian_web",
 *     indexes={
 *         @Index(name="tholian_web_ship_idx", columns={"ship_id"})
 *     }
 * )
 **/
class TholianWeb implements TholianWebInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $finished_time = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $ship_id = 0;

    /**
     *
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ShipInterface $webShip;

    /**
     * @var ArrayCollection<int, ShipInterface>
     *
     * @OneToMany(targetEntity="Ship", mappedBy="holdingWeb")
     */
    private Collection $capturedShips;

    public function __construct()
    {
        $this->capturedShips = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFinishedTime(): ?int
    {
        return $this->finished_time;
    }

    public function setFinishedTime(?int $time): TholianWebInterface
    {
        $this->finished_time = $time;

        return $this;
    }

    public function isFinished(): bool
    {
        //uninitialized
        if ($this->finished_time === 0) {
            return false;
        }

        //finished
        if ($this->finished_time === null) {
            return true;
        }

        return $this->finished_time < time();
    }

    public function getUser(): UserInterface
    {
        return $this->webShip->getUser();
    }

    public function getWebShip(): ShipInterface
    {
        return $this->webShip;
    }

    public function setWebShip(ShipInterface $webShip): TholianWebInterface
    {
        $this->webShip = $webShip;

        return $this;
    }

    public function getCapturedShips(): Collection
    {
        return $this->capturedShips;
    }

    public function updateFinishTime(int $time): void
    {
        $this->finished_time = $time;
    }
}

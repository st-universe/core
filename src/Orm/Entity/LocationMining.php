<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\LocationMiningRepository;

#[Table(name: 'stu_location_mining')]
#[Entity(repositoryClass: LocationMiningRepository::class)]
class LocationMining
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $location_id;

    #[Column(type: 'integer')]
    private int $commodity_id;

    #[Column(type: 'integer')]
    private int $actual_amount;

    #[Column(type: 'integer')]
    private int $max_amount;

    #[Column(type: 'integer', nullable: true)]
    private ?int $depleted_at = null;

    #[ManyToOne(targetEntity: Location::class, inversedBy: 'locationMinings')]
    #[JoinColumn(name: 'location_id', nullable: false, referencedColumnName: 'id')]
    private Location $location;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id')]
    private Commodity $commodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocationId(): int
    {
        return $this->location_id;
    }

    public function setLocationId(int $location_id): void
    {
        $this->location_id = $location_id;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodity_id): void
    {
        $this->commodity_id = $commodity_id;
    }

    public function getActualAmount(): int
    {
        return $this->actual_amount;
    }

    public function setActualAmount(int $actual_amount): void
    {
        $this->actual_amount = $actual_amount;
    }

    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }

    public function setMaxAmount(int $max_amount): void
    {
        $this->max_amount = $max_amount;
    }

    public function getDepletedAt(): ?int
    {
        return $this->depleted_at;
    }

    public function setDepletedAt(?int $depleted_at): void
    {
        $this->depleted_at = $depleted_at;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(Commodity $commodity): void
    {
        $this->commodity = $commodity;
    }
}

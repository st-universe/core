<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
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
class LocationMining implements LocationMiningInterface
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

    #[ManyToOne(targetEntity: Location::class)]
    #[JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    private LocationInterface $location;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id')]
    private CommodityInterface $commodity;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getLocationId(): int
    {
        return $this->location_id;
    }

    #[Override]
    public function setLocationId(int $location_id): void
    {
        $this->location_id = $location_id;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(int $commodity_id): void
    {
        $this->commodity_id = $commodity_id;
    }

    #[Override]
    public function getActualAmount(): int
    {
        return $this->actual_amount;
    }

    #[Override]
    public function setActualAmount(int $actual_amount): void
    {
        $this->actual_amount = $actual_amount;
    }

    #[Override]
    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }

    #[Override]
    public function setMaxAmount(int $max_amount): void
    {
        $this->max_amount = $max_amount;
    }

    #[Override]
    public function getDepletedAt(): ?int
    {
        return $this->depleted_at;
    }

    #[Override]
    public function setDepletedAt(?int $depleted_at): void
    {
        $this->depleted_at = $depleted_at;
    }

    #[Override]
    public function getLocation(): LocationInterface
    {
        return $this->location;
    }

    #[Override]
    public function setLocation(LocationInterface $location): void
    {
        $this->location = $location;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function setCommodity(CommodityInterface $commodity): void
    {
        $this->commodity = $commodity;
    }
}

<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;


use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\MiningQueueRepository;

#[Table(name: 'stu_mining_queue')]
#[Entity(repositoryClass: MiningQueueRepository::class)]
class MiningQueue
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $ship_id;

    #[Column(type: 'integer')]
    private int $location_mining_id;

    #[ManyToOne(targetEntity: LocationMining::class)]
    #[JoinColumn(name: 'location_mining_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private LocationMining $locationMining;

    #[OneToOne(targetEntity: Ship::class)]
    #[JoinColumn(name: 'ship_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Ship $ship;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function setShip(Ship $ship): MiningQueue
    {
        $this->ship = $ship;
        return $this;
    }

    public function getShip(): Ship
    {
        return $this->ship;
    }

    public function getLocationMiningId(): int
    {
        return $this->location_mining_id;
    }

    public function setLocationMining(LocationMining $locationMining): MiningQueue
    {
        $this->locationMining = $locationMining;
        return $this;
    }

    public function getLocationMining(): LocationMining
    {
        return $this->locationMining;
    }
}

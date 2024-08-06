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
use Override;
use Stu\Orm\Repository\MiningQueueRepository;

#[Table(name: 'stu_mining_queue')]
#[Entity(repositoryClass: MiningQueueRepository::class)]
class MiningQueue implements MiningQueueInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $ship_id;

    #[Column(type: 'integer')]
    private int $location_mining_id;

    #[ManyToOne(targetEntity: 'LocationMining')]
    #[JoinColumn(name: 'location_mining_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private LocationMiningInterface $locationMining;

    #[OneToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $ship;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getShipId(): int
    {
        return $this->ship_id;
    }

    #[Override]
    public function setShip(ShipInterface $ship): MiningQueueInterface
    {
        $this->ship = $ship;
        return $this;
    }

    #[Override]
    public function getShip(): ShipInterface
    {
        return $this->ship;
    }
}

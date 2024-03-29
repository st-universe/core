<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_torpedo_storage')]
#[Index(name: 'torpedo_storage_ship_idx', columns: ['ship_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\TorpedoStorageRepository')]
class TorpedoStorage implements TorpedoStorageInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $ship_id;

    #[Column(type: 'integer', length: 3)]
    private int $torpedo_type;

    #[OneToOne(targetEntity: 'Ship', inversedBy: 'torpedoStorage')]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $ship;

    #[ManyToOne(targetEntity: 'TorpedoType')]
    #[JoinColumn(name: 'torpedo_type', referencedColumnName: 'id')]
    private TorpedoTypeInterface $torpedo;

    #[OneToOne(targetEntity: 'Storage', mappedBy: 'torpedoStorage')]
    private StorageInterface $storage;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): TorpedoStorageInterface
    {
        $this->ship = $ship;
        return $this;
    }

    public function getTorpedo(): TorpedoTypeInterface
    {
        return $this->torpedo;
    }

    public function setTorpedo(TorpedoTypeInterface $torpedoType): TorpedoStorageInterface
    {
        $this->torpedo = $torpedoType;
        return $this;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function setStorage(StorageInterface $storage): TorpedoStorageInterface
    {
        $this->storage = $storage;

        return $this;
    }
}

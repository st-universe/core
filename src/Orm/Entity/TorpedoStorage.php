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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TorpedoStorageRepository")
 * @Table(
 *     name="stu_torpedo_storage"
 * )
 **/
class TorpedoStorage implements TorpedoStorageInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $ship_id;

    /**
     * @Column(type="integer", length=3)
     *
     * @var int
     */
    private $torpedo_type;

    /**
     * @var ShipInterface
     *
     * @OneToOne(targetEntity="Ship", inversedBy="torpedoStorage")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    /**
     * @var TorpedoTypeInterface
     *
     * @ManyToOne(targetEntity="TorpedoType")
     * @JoinColumn(name="torpedo_type", referencedColumnName="id")
     */
    private $torpedo;

    /**
     * @var StorageInterface
     *
     * @OneToOne(targetEntity="Storage", mappedBy="torpedoStorage")
     */
    private $storage;

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

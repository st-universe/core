<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
     */
    private $id;

    /** @Column(type="integer") * */
    private $ship_id;

    /** @Column(type="integer", length=3) */
    private $torpedo_type;

    /**
     * @OneToOne(targetEntity="Ship", inversedBy="torpedoStorage")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    /**
     * @ManyToOne(targetEntity="TorpedoType")
     * @JoinColumn(name="torpedo_type", referencedColumnName="id")
     */
    private $torpedo;

    /**
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

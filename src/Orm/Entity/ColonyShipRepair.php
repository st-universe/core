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
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyShipRepairRepository")
 * @Table(name="stu_colonies_shiprepair")
 */
class ColonyShipRepair implements ColonyShipRepairInterface
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
    private $colony_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $ship_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $field_id;

    /**
     * @var ColonyInterface
     *
     * @ManyToOne(targetEntity="Colony")
     * @JoinColumn(name="colony_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $colony;

    /**
     * @var ShipInterface
     *
     * @OneToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    /** @var null|PlanetFieldInterface */
    private $field;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyId(): int
    {
        return $this->colony_id;
    }

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function setFieldId(int $field_id): ColonyShipRepairInterface
    {
        $this->field_id = $field_id;

        return $this;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getField(): PlanetFieldInterface
    {
        if ($this->field === null) {
            // @todo refactor
            global $container;

            $this->field = $container->get(PlanetFieldRepositoryInterface::class)->getByColonyAndFieldId(
                $this->getColonyId(),
                $this->getFieldId()
            );
        }
        return $this->field;
    }

    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    public function setColony(ColonyInterface $colony): ColonyShipRepairInterface
    {
        $this->colony = $colony;
        return $this;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): ColonyShipRepairInterface
    {
        $this->ship = $ship;
        return $this;
    }
}

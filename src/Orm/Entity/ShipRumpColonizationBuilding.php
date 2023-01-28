<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpColonizationBuildingRepository")
 * @Table(
 *     name="stu_rumps_colonize_building",
 *     indexes={
 *         @Index(name="rump_colonize_building_ship_rump_idx", columns={"rump_id"})
 *     }
 * )
 */
class ShipRumpColonizationBuilding implements ShipRumpColonizationBuildingInterface
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
    private $rump_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $building_id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function setRumpId(int $shipRumpId): ShipRumpColonizationBuildingInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getBuildingId(): int
    {
        return $this->building_id;
    }

    public function setBuildingId(int $buildingId): ShipRumpColonizationBuildingInterface
    {
        $this->building_id = $buildingId;

        return $this;
    }
}

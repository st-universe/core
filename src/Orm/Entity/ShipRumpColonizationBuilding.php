<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpColonizationBuildingRepository")
 * @Table(
 *     name="stu_rumps_colonize_building",
 *     indexes={
 *         @Index(name="ship_rump_idx", columns={"rump_id"})
 *     }
 * )
 **/
class ShipRumpColonizationBuilding implements ShipRumpColonizationBuildingInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $rump_id = 0;

    /** @Column(type="integer") * */
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

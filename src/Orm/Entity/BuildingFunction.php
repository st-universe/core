<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingFunctionRepository")
 * @Table(
 *     name="stu_buildings_functions",
 *     indexes={
 *         @Index(name="building_idx", columns={"buildings_id"}),
 *         @Index(name="function_idx", columns={"function"})
 *     }
 * )
 **/
class BuildingFunction implements BuildingFunctionInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $buildings_id = 0;

    /** @Column(type="smallint") */
    private $function = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    public function setBuildingId(int $buildingId): BuildingFunctionInterface
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getFunction(): int
    {
        return $this->function;
    }

    public function setFunction(int $function): BuildingFunctionInterface
    {
        $this->function = $function;

        return $this;
    }
}

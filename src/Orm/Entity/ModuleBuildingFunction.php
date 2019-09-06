<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ModuleBuildingFunctionRepository")
 * @Table(
 *     name="stu_modules_buildingfunction",
 *     indexes={
 *         @Index(name="module_buildingfunction_idx", columns={"module_id", "buildingfunction"})
 *     }
 * )
 **/
class ModuleBuildingFunction implements ModuleBuildingFunctionInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $module_id = 0;

    /** @Column(type="integer") * */
    private $buildingfunction = 0;

    /**
     * @ManyToOne(targetEntity="Module")
     * @JoinColumn(name="module_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): ModuleBuildingFunctionInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getBuildingFunction(): int
    {
        return $this->buildingfunction;
    }

    public function setBuildingFunction(int $buildingFunction): ModuleBuildingFunctionInterface
    {
        $this->buildingfunction = $buildingFunction;
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }
}

<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use ModulesData;

/**
 * @Entity
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildplanModuleRepository")
 * @Table(
 *     name="stu_buildplans_modules",
 *     uniqueConstraints={@UniqueConstraint(name="buildplan_module_type_idx", columns={"buildplan_id", "module_type"})}
 * )
 **/
class BuildplanModule implements BuildplanModuleInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $buildplan_id = 0;

    /** @Column(type="smallint") * */
    private $module_type = 0;

    /** @Column(type="integer") * */
    private $module_id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildplanid(): int
    {
        return $this->buildplan_id;
    }

    public function setBuildplanId(int $buildplanId): BuildplanModuleInterface
    {
        $this->buildplan_id = $buildplanId;

        return $this;
    }

    public function getModuleType(): int
    {
        return $this->module_type;
    }

    public function setModuleType(int $moduleType): BuildplanModuleInterface
    {
        $this->module_type = $moduleType;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): BuildplanModuleInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getModule(): ModulesData {
        return ResourceCache()->getObject(CACHE_MODULE, $this->getModuleId());
    }
}

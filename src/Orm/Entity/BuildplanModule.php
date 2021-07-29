<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildplanModuleRepository")
 * @Table(
 *     name="stu_buildplans_modules",
 *     uniqueConstraints={@UniqueConstraint(name="buildplan_module_type_idx", columns={"buildplan_id", "module_type", "module_special"})}
 * )
 **/
class BuildplanModule implements BuildplanModuleInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $buildplan_id = 0;

    /** @Column(type="smallint") * */
    private $module_type = 0;

    /** @Column(type="integer") * */
    private $module_id = 0;

    /** @Column(type="smallint", nullable=true) * */
    private $module_special;

    /** @Column(type="smallint") * */
    private $module_count = 1;

    /**
     * @ManyToOne(targetEntity="Module")
     * @JoinColumn(name="module_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $module;

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

    public function getModuleSpecial(): ?int
    {
        return $this->module_special;
    }

    public function setModuleSpecial(?int $moduleSpecial): BuildplanModuleInterface
    {
        $this->module_special = $moduleSpecial;

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

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function setModule(ModuleInterface $module): BuildplanModuleInterface
    {
        $this->module = $module;

        return $this;
    }
}

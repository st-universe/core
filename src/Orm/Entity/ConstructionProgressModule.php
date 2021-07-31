<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ConstructionProgressModuleRepository")
 * @Table(
 *     name="stu_progress_module"
 * )
 **/
class ConstructionProgressModule implements ConstructionProgressModuleInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $progress_id = 0;

    /** @Column(type="integer") * */
    private $module_id = 0;

    /**
     * @ManyToOne(targetEntity="ConstructionProgress")
     * @JoinColumn(name="progress_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $progress;

    /**
     * @ManyToOne(targetEntity="Module")
     * @JoinColumn(name="module_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getConstructionProgress(): ConstructionProgressInterface
    {
        return $this->progress;
    }

    public function setConstructionProgress(ConstructionProgressInterface $progress): ConstructionProgressModuleInterface
    {
        $this->progress = $progress;
        return $this;
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function setModule(ModuleInterface $module): ConstructionProgressModuleInterface
    {
        $this->module = $module;

        return $this;
    }
}

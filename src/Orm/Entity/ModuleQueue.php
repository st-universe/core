<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ModuleQueueRepository")
 * @Table(
 *     name="stu_modules_queue",
 *     indexes={
 *         @Index(name="colony_module_idx", columns={"colony_id", "module_id"})
 *     }
 * )
 **/
class ModuleQueue implements ModuleQueueInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $colony_id = 0;

    /** @Column(type="integer") * */
    private $module_id = 0;

    /** @Column(type="integer") * */
    private $count = 0;

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

    public function getColonyId(): int
    {
        return $this->colony_id;
    }

    public function setColonyId(int $colonyId): ModuleQueueInterface
    {
        $this->colony_id = $colonyId;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): ModuleQueueInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): ModuleQueueInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getBuildingFunction(): int
    {
        return $this->buildingfunction;
    }

    public function setBuildingFunction(int $buildingFunction): ModuleQueueInterface
    {
        $this->buildingfunction = $buildingFunction;

        return $this;
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function setModule(ModuleInterface $module): ModuleQueueInterface
    {
        $this->module = $module;

        return $this;
    }
}

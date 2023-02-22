<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ModuleCostRepository")
 * @Table(
 *     name="stu_modules_cost",
 *     indexes={
 *         @Index(name="module_cost_module_idx", columns={"module_id"})
 *     }
 * )
 **/
class ModuleCost implements ModuleCostInterface
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
    private $module_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $commodity_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $count = 0;

    /**
     * @var CommodityInterface
     *
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @var ModuleInterface
     *
     * @ManyToOne(targetEntity="Module", inversedBy="buildingCosts")
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

    public function setModuleId(int $moduleId): ModuleCostInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): ModuleCostInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): ModuleCostInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }
}

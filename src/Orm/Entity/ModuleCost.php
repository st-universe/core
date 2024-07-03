<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_modules_cost')]
#[Index(name: 'module_cost_module_idx', columns: ['module_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ModuleCostRepository')]
class ModuleCost implements ModuleCostInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'integer')]
    private int $count = 0;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    /**
     * @var ModuleInterface
     */
    #[ManyToOne(targetEntity: 'Module', inversedBy: 'buildingCosts')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $module;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getModuleId(): int
    {
        return $this->module_id;
    }

    #[Override]
    public function setModuleId(int $moduleId): ModuleCostInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(int $commodityId): ModuleCostInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->count;
    }

    #[Override]
    public function setAmount(int $amount): ModuleCostInterface
    {
        $this->count = $amount;

        return $this;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }
}

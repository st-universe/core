<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\CommodityInterface;

final class ShuttleManagementItem
{
    private CommodityInterface $commodity;

    private int $currentLoad = 0;

    private int $colonyLoad = 0;

    public function __construct(CommodityInterface $commodity)
    {
        $this->commodity = $commodity;
    }

    public function setCurrentLoad(int $load): void
    {
        $this->currentLoad = $load;
    }

    public function getCurrentLoad(): int
    {
        return $this->currentLoad;
    }

    public function setColonyLoad(int $load): void
    {
        $this->colonyLoad = $load;
    }

    public function getColonyLoad(): int
    {
        return $this->colonyLoad;
    }

    public function getMaxUnits(): int
    {
        return $this->getCurrentLoad() + $this->getColonyLoad();
    }

    public function getCommodityId(): int
    {
        return $this->commodity->getId();
    }

    public function getCommodityName(): string
    {
        return $this->commodity->getName();
    }
}

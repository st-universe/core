<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\CommodityInterface;

final class ShuttleManagementItem
{
    private CommodityInterface $commodity;

    private int $currentLoad;

    private int $colonyLoad;

    public function __construct(CommodityInterface $commodity)
    {
        $this->commodity = $commodity;
    }

    public function setCurrentLoad(int $load): void
    {
        $this->currentLoad = $load;
    }

    public function setColonyLoad(int $load): void
    {
        $this->colonyLoad = $load;
    }

    public function getCommodityId(): int
    {
        return $this->commodity->getId();
    }
}

<?php

namespace Stu\Module\Station\View\ShowStationCosts;

use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Storage;

class StationCostWrapper
{
    public function __construct(private BuildplanModule $buildplanModule, private ?Storage $shipStorage)
    {
    }

    public function getCommodityId(): int
    {
        return $this->buildplanModule->getModule()->getCommodityId();
    }

    public function getName(): string
    {
        return $this->buildplanModule->getModule()->getName();
    }

    public function getLevel(): int
    {
        return $this->buildplanModule->getModule()->getLevel();
    }

    public function getNeededAmount(): int
    {
        return $this->buildplanModule->getModuleCount();
    }

    public function getStoredAmount(): int
    {
        return $this->shipStorage !== null ? $this->shipStorage->getAmount() : 0;
    }

    public function getColor(): string
    {
        if ($this->getStoredAmount() < $this->getNeededAmount()) {
            return 'red';
        } else {
            return 'green';
        }
    }
}

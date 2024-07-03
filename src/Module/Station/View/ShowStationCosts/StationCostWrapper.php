<?php

namespace Stu\Module\Station\View\ShowStationCosts;

use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\StorageInterface;

class StationCostWrapper
{
    public function __construct(private BuildplanModuleInterface $buildplanModule, private ?StorageInterface $shipStorage)
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

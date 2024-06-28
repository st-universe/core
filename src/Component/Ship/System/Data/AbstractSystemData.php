<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Tal\TalStatusBar;
use Stu\Module\Tal\TalStatusBarInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

abstract class AbstractSystemData
{
    protected ShipInterface $ship;

    public function __construct(private ShipSystemRepositoryInterface $shipSystemRepository)
    {
    }

    public function setShip(ShipInterface $ship): void
    {
        $this->ship = $ship;
    }

    abstract public function getSystemType(): ShipSystemTypeEnum;

    /**
     * updates the system metadata for this specific ship system
     */
    public function update(): void
    {
        $system = $this->ship->getShipSystem($this->getSystemType());
        $system->setData(json_encode($this, JSON_THROW_ON_ERROR));
        $this->shipSystemRepository->save($system);
    }

    protected function getTalStatusBar(string $label, int $value, int $maxValue, string $color): TalStatusBarInterface
    {
        return (new TalStatusBar())
            ->setColor($color)
            ->setLabel($label)
            ->setMaxValue($maxValue)
            ->setValue($value);
    }
}

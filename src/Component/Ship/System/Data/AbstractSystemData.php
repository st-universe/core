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

    public function setShip(ShipInterface $ship): void
    {
        $this->ship = $ship;
    }

    abstract public function update(): void;

    /**
     * updates the system metadata for this specific ship system
     */
    protected function updateSystemData(
        ShipSystemTypeEnum $systemType,
        AbstractSystemData $data,
        ShipSystemRepositoryInterface $shipSystemRepository
    ): void {
        $system = $this->ship->getShipSystem($systemType);
        $system->setData(json_encode($data, JSON_THROW_ON_ERROR));
        $shipSystemRepository->save($system);
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

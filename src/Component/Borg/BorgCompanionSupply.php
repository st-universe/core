<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Ship;

final class BorgCompanionSupply
{
    public function __construct(
        private BorgSphereEnergy $energy,
        private BorgSphereTorpedoSupply $torpedoes,
        private SpacecraftWrapperFactoryInterface $wrappers,
        private SpacecraftSystemManagerInterface $systems
    ) {}

    public function refill(Ship $leader): void
    {
        foreach ($leader->getFleet()?->getShips() ?? [] as $ship) {
            if (!$this->isCompanion($leader, $ship)) {
                continue;
            }
            $this->energy->recharge($ship);
            $this->torpedoes->refill($ship);
            $this->wake($ship);
        }
    }

    public function leaveWarp(Ship $leader): void
    {
        foreach ($leader->getFleet()?->getShips() ?? [] as $ship) {
            if ($this->isCompanion($leader, $ship) && $ship->getWarpDriveState()) {
                $this->systems->deactivate($this->wrappers->wrapShip($ship), SpacecraftSystemTypeEnum::WARPDRIVE, true);
            }
        }
    }

    private function isCompanion(Ship $leader, Ship $ship): bool
    {
        return $ship !== $leader && $ship->getLocation() === $leader->getLocation()
            && $ship->getUser() === $leader->getUser() && !$ship->getCondition()->isDestroyed()
            && !$ship->getCondition()->isDisabled() && $ship->getAssimilation()?->isComplete() === true;
    }

    private function wake(Ship $ship): void
    {
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO, SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::IMPULSEDRIVE] as $type) {
            if ($ship->isSystemHealthy($type)) {
                $ship->getSpacecraftSystem($type)->setMode(SpacecraftSystemModeEnum::MODE_ON);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;

final class BorgSphereRegeneration
{
    public function regenerate(BorgSphereEvent $event, Ship $ship, int $steps): void
    {
        if ($event->getFirstDisabledAt() === null) {
            return;
        }
        $configuration = $event->getConfiguration();
        $percent = $configuration->getSetting('normal_regen_percent') * $steps;
        $condition = $ship->getCondition();
        $condition->setHull(min($ship->getMaxHull(), $condition->getHull() + (int) ceil($ship->getMaxHull() * $percent / 100)))
            ->setShield(min($ship->getMaxShield(true), $condition->getShield() + (int) ceil($ship->getMaxShield(true) * $percent / 100)));
        $progress = min($configuration->getSetting('transwarp_escape_bp'), $event->getTranswarpProgress() + $configuration->getSetting('transwarp_regen_bp') * $steps);
        $event->setTranswarpProgress($progress);
        $floorStep = $configuration->getSetting('transwarp_floor_step_bp');
        $event->setTranswarpFloor(max($event->getTranswarpFloor(), min($configuration->getSetting('transwarp_escape_bp') - $floorStep, intdiv($progress, $floorStep) * $floorStep)));
        foreach ($ship->getSystems() as $system) {
            $system->setStatus($system->getSystemType() === SpacecraftSystemTypeEnum::TRANSWARP_COIL ? (int) floor($progress / 100) : min(100, max(10, $system->getStatus() + $percent)));
        }
        if ($event->getPhase() === BorgSpherePhaseEnum::REGENERATING && $this->isReady($event, $ship)) {
            $condition->setDisabled(false);
            $event->setPhase(BorgSpherePhaseEnum::ROAMING);
            $this->wake($ship);
        }
    }

    private function isReady(BorgSphereEvent $event, Ship $ship): bool
    {
        $threshold = $event->getConfiguration()->getSetting('wake_percent');
        if ($ship->getCondition()->getHull() * 100 < $ship->getMaxHull() * $threshold || $ship->getCondition()->getShield() * 100 < $ship->getMaxShield(true) * $threshold) {
            return false;
        }
        foreach ($ship->getSystems() as $system) {
            if ($system->getModule() !== null && $system->getSystemType() !== SpacecraftSystemTypeEnum::TRANSWARP_COIL && $system->getStatus() < $threshold) {
                return false;
            }
        }
        return true;
    }

    private function wake(Ship $ship): void
    {
        foreach ($ship->getSystems() as $system) {
            if ($system->getMode() === SpacecraftSystemModeEnum::MODE_OFF && in_array($system->getSystemType(), [SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO, SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::IMPULSEDRIVE], true)) {
                $system->setMode(SpacecraftSystemModeEnum::MODE_ON);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;

final class BorgSphereRegeneration
{
    public function __construct(private StuRandom $random) {}

    public function regenerate(BorgSphereEvent $event, Ship $ship, int $steps): void
    {
        if ($event->getFirstDisabledAt() === null || $steps < 1) {
            return;
        }
        $configuration = $event->getConfiguration();
        for ($hour = 0; $hour < $steps; $hour++) {
            $this->regenerateNormalSystems($configuration, $ship);
        }
        $progress = min($configuration->getSetting('transwarp_escape_bp'), $event->getTranswarpProgress() + $configuration->getSetting('transwarp_regen_bp') * $steps);
        $event->setTranswarpProgress($progress);
        $floorStep = $configuration->getSetting('transwarp_floor_step_bp');
        $event->setTranswarpFloor(max($event->getTranswarpFloor(), min($configuration->getSetting('transwarp_escape_bp') - $floorStep, intdiv($progress, $floorStep) * $floorStep)));
        $ship->getSystems()->get(SpacecraftSystemTypeEnum::TRANSWARP_COIL->value)?->setStatus((int) floor($progress / 100));
        if ($event->getPhase() === BorgSpherePhaseEnum::REGENERATING && $this->isReady($event, $ship)) {
            $ship->getCondition()->setDisabled(false);
            $event->setPhase(BorgSpherePhaseEnum::ROAMING);
            $this->wake($ship);
        }
    }

    private function regenerateNormalSystems(BorgSphereConfiguration $configuration, Ship $ship): void
    {
        $condition = $ship->getCondition();
        $condition->setHull($this->regenerateValue($condition->getHull(), $ship->getMaxHull(), $configuration))
            ->setShield($this->regenerateValue($condition->getShield(), $ship->getMaxShield(true), $configuration));
        foreach ($ship->getSystems() as $system) {
            if ($system->getSystemType() !== SpacecraftSystemTypeEnum::TRANSWARP_COIL) {
                $system->setStatus($this->regenerateValue($system->getStatus(), 100, $configuration));
            }
        }
    }

    private function regenerateValue(int $current, int $maximum, BorgSphereConfiguration $configuration): int
    {
        $percent = $this->random->rand($configuration->getSetting('normal_regen_min_percent'), $configuration->getSetting('normal_regen_max_percent'));
        return min($maximum, $current + (int) ceil($maximum * $percent / 100));
    }

    private function isReady(BorgSphereEvent $event, Ship $ship): bool
    {
        $threshold = $event->getConfiguration()->getSetting('wake_percent');
        if ($ship->getCondition()->getHull() * 100 > $ship->getMaxHull() * $threshold || $ship->getCondition()->getShield() * 100 > $ship->getMaxShield(true) * $threshold) {
            return true;
        }
        foreach ($ship->getSystems() as $system) {
            if ($system->getSystemType() !== SpacecraftSystemTypeEnum::TRANSWARP_COIL && $system->getStatus() > $threshold) {
                return true;
            }
        }
        return false;
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

<?php

namespace Stu\Component\Spacecraft\System;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface SpacecraftSystemTypeInterface
{
    public function setSpacecraft(SpacecraftInterface $spacecraft): void;

    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool;

    public function checkDeactivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool;

    public function getEnergyUsageForActivation(): int;

    public function getEnergyConsumption(): int;

    /**
     * the higher the number, the more important the system is
     */
    public function getPriority(): int;

    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void;

    public function deactivate(SpacecraftWrapperInterface $wrapper): void;

    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void;

    public function handleDamage(SpacecraftWrapperInterface $wrapper): void;

    public function getDefaultMode(): SpacecraftSystemModeEnum;

    public function getCooldownSeconds(): ?int;
}

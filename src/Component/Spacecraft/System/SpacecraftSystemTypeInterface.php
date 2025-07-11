<?php

namespace Stu\Component\Spacecraft\System;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

interface SpacecraftSystemTypeInterface
{
    public function setSpacecraft(Spacecraft $spacecraft): void;

    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool;

    public function checkDeactivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool;

    public function getEnergyUsageForActivation(): int;

    public function getEnergyConsumption(): int;

    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void;

    public function deactivate(SpacecraftWrapperInterface $wrapper): void;

    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void;

    public function handleDamage(SpacecraftWrapperInterface $wrapper): void;

    public function getDefaultMode(): SpacecraftSystemModeEnum;

    public function getCooldownSeconds(): ?int;
}

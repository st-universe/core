<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

abstract class AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    protected Spacecraft $spacecraft;

    #[\Override]
    public function setSpacecraft(Spacecraft $spacecraft): void
    {
        $this->spacecraft = $spacecraft;
    }

    #[\Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        $wrapper->get()->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_ON);
    }

    #[\Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $wrapper->get()->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_OFF);
    }

    #[\Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        return true;
    }

    #[\Override]
    public function checkDeactivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        return true;
    }

    #[\Override]
    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    #[\Override]
    public function getEnergyConsumption(): int
    {
        return 1;
    }

    #[\Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    #[\Override]
    public function handleDamage(SpacecraftWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    #[\Override]
    public function getDefaultMode(): SpacecraftSystemModeEnum
    {
        return SpacecraftSystemModeEnum::MODE_OFF;
    }

    #[\Override]
    public function canBeActivatedWithInsufficientCrew(): bool
    {
        return false;
    }

    #[\Override]
    public function getCooldownSeconds(): ?int
    {
        return null;
    }
}

<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemPriorities;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

abstract class AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    protected SpacecraftInterface $spacecraft;

    #[Override]
    public function setSpacecraft(SpacecraftInterface $spacecraft): void
    {
        $this->spacecraft = $spacecraft;
    }

    abstract public function getSystemType(): SpacecraftSystemTypeEnum;

    #[Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        $wrapper->get()->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_ON);
    }

    #[Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $wrapper->get()->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_OFF);
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        return true;
    }

    #[Override]
    public function checkDeactivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        return true;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    #[Override]
    public function getPriority(): int
    {
        if (array_key_exists($this->getSystemType()->value, SpacecraftSystemPriorities::PRIORITIES)) {
            return SpacecraftSystemPriorities::PRIORITIES[$this->getSystemType()->value];
        }

        return SpacecraftSystemPriorities::PRIORITY_STANDARD;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 1;
    }

    #[Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    #[Override]
    public function handleDamage(SpacecraftWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    #[Override]
    public function getDefaultMode(): SpacecraftSystemModeEnum
    {
        return SpacecraftSystemModeEnum::MODE_OFF;
    }

    #[Override]
    public function getCooldownSeconds(): ?int
    {
        return null;
    }
}

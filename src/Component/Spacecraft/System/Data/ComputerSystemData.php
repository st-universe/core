<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Map\DirectionEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class ComputerSystemData extends AbstractSystemData
{
    public int $hitChance = 0;
    public int $evadeChance = 0;
    public bool $isInEmergency = false;
    public DirectionEnum $flightDirection = DirectionEnum::NON;
    public SpacecraftAlertStateEnum $alertState = SpacecraftAlertStateEnum::ALERT_GREEN;

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::COMPUTER;
    }

    public function getHitChance(): int
    {
        return (int) (ceil($this->hitChance
            * $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER)->getStatus() / 100));
    }

    public function setHitChance(int $hitChance): ComputerSystemData
    {
        $this->hitChance = $hitChance;
        return $this;
    }

    public function getEvadeChance(): int
    {
        if (!$this->spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE)) {
            return $this->evadeChance;
        }

        return (int) (ceil($this->evadeChance
            * $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE)->getStatus() / 100));
    }

    public function setEvadeChance(int $evadeChance): ComputerSystemData
    {
        $this->evadeChance = $evadeChance;
        return $this;
    }

    public function isInEmergency(): bool
    {
        return $this->isInEmergency;
    }

    public function setIsInEmergency(bool $isInEmergency): ComputerSystemData
    {
        $this->isInEmergency = $isInEmergency;
        return $this;
    }

    public function getFlightDirection(): DirectionEnum
    {
        return $this->flightDirection;
    }

    public function setFlightDirection(DirectionEnum $direction): ComputerSystemData
    {
        $this->flightDirection = $direction;
        return $this;
    }

    public function getAlertState(): SpacecraftAlertStateEnum
    {
        return $this->alertState;
    }

    public function setAlertState(SpacecraftAlertStateEnum $alertState): ComputerSystemData
    {
        $this->alertState = $alertState;
        return $this;
    }

    public function isAlertGreen(): bool
    {
        return $this->alertState === SpacecraftAlertStateEnum::ALERT_GREEN;
    }

    public function setAlertStateGreen(): ComputerSystemData
    {
        return $this->setAlertState(SpacecraftAlertStateEnum::ALERT_GREEN);
    }
}

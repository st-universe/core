<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Spacecraft\SpacecraftStateEnum;

interface SpacecraftConditionInterface
{
    public function getSpacecraft(): SpacecraftInterface;

    public function getHull(): int;

    public function setHull(int $hull): SpacecraftConditionInterface;

    public function changeHull(int $amount): SpacecraftConditionInterface;

    public function getShield(): int;

    public function setShield(int $schilde): SpacecraftConditionInterface;

    public function changeShield(int $amount): SpacecraftConditionInterface;

    public function isDestroyed(): bool;

    public function setIsDestroyed(bool $isDestroyed): SpacecraftConditionInterface;

    public function isDisabled(): bool;

    public function setDisabled(bool $isDisabled): SpacecraftConditionInterface;

    public function getState(): SpacecraftStateEnum;

    public function setState(SpacecraftStateEnum $state): SpacecraftConditionInterface;

    public function isUnderRepair(): bool;

    public function isUnderRetrofit(): bool;
}

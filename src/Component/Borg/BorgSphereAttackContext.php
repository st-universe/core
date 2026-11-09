<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;

final class BorgSphereAttackContext
{
    private ?int $destructionTarget = null;
    private ?int $transwarpTarget = null;
    private bool $battleStopped = false;
    private ?Location $automaticAttackLocation = null;

    public function begin(Spacecraft $target, bool $destructionAllowed, bool $transwarpTargeted): void
    {
        $this->destructionTarget = $destructionAllowed ? $target->getId() : null;
        $this->transwarpTarget = $transwarpTargeted ? $target->getId() : null;
        $this->battleStopped = false;
        $this->automaticAttackLocation = null;
    }

    public function beginAutomaticAttack(Spacecraft $source): void
    {
        $this->begin($source, false, false);
        $this->automaticAttackLocation = $source->getLocation();
    }

    public function allowsAutomaticAttackTarget(Spacecraft $target): bool
    {
        return $this->automaticAttackLocation === null
            || ($target->getLocation() === $this->automaticAttackLocation
                && !$target->isWarped() && !$target->isCloaked() && !$target->getRump()->isEscapePods()
                && !$target->isRpgModuleInvisible());
    }

    public function allowsDestruction(Spacecraft $target): bool
    {
        return $this->destructionTarget === $target->getId();
    }

    public function targetsTranswarp(Spacecraft $target): bool
    {
        return $this->transwarpTarget === $target->getId();
    }

    public function clear(): void
    {
        $this->destructionTarget = null;
        $this->transwarpTarget = null;
        $this->battleStopped = false;
        $this->automaticAttackLocation = null;
    }

    public function beginBattle(): void
    {
        $this->battleStopped = false;
    }

    public function stopBattleAfterDisabling(Spacecraft $target): void
    {
        if (!$this->allowsDestruction($target)) {
            $this->battleStopped = true;
        }
    }

    public function isBattleStopped(): bool
    {
        return $this->battleStopped;
    }

    public function endBattle(): void
    {
        $this->battleStopped = false;
    }
}

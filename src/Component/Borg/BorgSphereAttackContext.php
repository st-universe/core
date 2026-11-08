<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Orm\Entity\Spacecraft;

final class BorgSphereAttackContext
{
    private ?int $destructionTarget = null;
    private ?int $transwarpTarget = null;

    public function begin(Spacecraft $target, bool $destructionAllowed, bool $transwarpTargeted): void
    {
        $this->destructionTarget = $destructionAllowed ? $target->getId() : null;
        $this->transwarpTarget = $transwarpTargeted ? $target->getId() : null;
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
    }
}

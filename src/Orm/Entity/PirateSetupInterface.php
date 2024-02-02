<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface PirateSetupInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getProbabilityWeight(): int;

    /**
     * @return Collection<int, PirateSetupBuildplanInterface>
     */
    public function getSetupBuildplans(): Collection;
}

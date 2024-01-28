<?php

namespace Stu\Orm\Entity\Pirates;

use Doctrine\Common\Collections\Collection;

interface PirateSetupInterface
{
    public function getId(): int;

    public function getName(): string;

    /**
     * @return Collection<PirateSetupBuildplanInterface>
     */
    public function getSetupBuildplans(): Collection;
}

<?php

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\UserInterface;

interface SpacecraftGroupInterface
{
    public function addSpacecraftWrapper(SpacecraftWrapperInterface $wrapper): void;

    /** @return Collection<int, SpacecraftWrapperInterface> */
    public function getWrappers(): Collection;

    public function getName(): string;

    public function getUser(): ?UserInterface;
}

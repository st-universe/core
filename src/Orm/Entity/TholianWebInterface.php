<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface TholianWebInterface extends SpacecraftInterface
{
    public function getFinishedTime(): ?int;

    public function setFinishedTime(?int $time): TholianWebInterface;

    public function isFinished(): bool;

    /**
     * @return Collection<int, SpacecraftInterface>
     */
    public function getCapturedSpacecrafts(): Collection;

    public function updateFinishTime(int $time): void;
}

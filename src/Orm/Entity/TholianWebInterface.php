<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface TholianWebInterface
{
    public function getId(): int;

    public function getFinishedTime(): ?int;

    public function setFinishedTime(?int $time): TholianWebInterface;

    public function isFinished(): bool;

    public function getUser(): UserInterface;

    public function getWebShip(): ShipInterface;

    public function setWebShip(ShipInterface $webShip): TholianWebInterface;

    /**
     * @return ShipInterface[]|Collection
     */
    public function getCapturedShips(): Collection;

    public function updateFinishTime(int $time): void;
}

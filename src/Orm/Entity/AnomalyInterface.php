<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;

interface AnomalyInterface extends SpacecraftDestroyerInterface
{
    public function getId(): int;

    public function getRemainingTicks(): int;

    public function setRemainingTicks(int $remainingTicks): AnomalyInterface;

    public function changeRemainingTicks(int $amount): AnomalyInterface;

    public function isActive(): bool;

    public function getAnomalyType(): AnomalyTypeInterface;

    public function setAnomalyType(AnomalyTypeInterface $anomalyType): AnomalyInterface;

    public function getLocation(): ?LocationInterface;

    public function setLocation(?LocationInterface $location): AnomalyInterface;

    public function getParent(): ?AnomalyInterface;

    public function setParent(?AnomalyInterface $anomaly): AnomalyInterface;

    public function getData(): ?string;

    public function setData(string $data): AnomalyInterface;

    public function getRoot(): AnomalyInterface;

    /** @return Collection<int, AnomalyInterface> */
    public function getChildren(): Collection;

    public function hasChildren(): bool;
}

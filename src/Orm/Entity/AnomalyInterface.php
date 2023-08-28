<?php

namespace Stu\Orm\Entity;

use Stu\Lib\Map\Location;

interface AnomalyInterface
{
    public function getId(): int;

    public function getRemainingTicks(): int;

    public function setRemainingTicks(int $remainingTicks): AnomalyInterface;

    public function isActive(): bool;

    public function getAnomalyType(): AnomalyTypeInterface;

    public function setAnomalyType(AnomalyTypeInterface $anomalyType): AnomalyInterface;

    public function getMap(): ?MapInterface;

    public function setMap(?MapInterface $map): AnomalyInterface;

    public function getStarsystemMap(): ?StarSystemMapInterface;

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): AnomalyInterface;

    public function getLocation(): Location;
}

<?php

namespace Stu\Orm\Entity;

interface AnomalyInterface
{
    public function getId(): int;

    public function getRemainingTicks(): int;

    public function setRemainingTicks(int $remainingTicks): AnomalyInterface;

    public function getAnomalyType(): AnomalyTypeInterface;

    public function setAnomalyType(AnomalyTypeInterface $anomalyType): AnomalyInterface;
}

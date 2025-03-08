<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;

class IonStormMovement
{
    public function __construct(
        private AnomalyRepositoryInterface $anomalyRepository,
        private StuRandom $stuRandom
    ) {}

    public function moveStorm(AnomalyInterface $root, IonStormData $ionStormData, LocationPool $locationPool): void
    {
        $horizontal = $ionStormData->getHorizontalMovement();
        $vertical = $ionStormData->getVerticalMovement();

        $children = $root->getChildren()->toArray();
        foreach ($children as $child) {
            $this->moveChild($child, $horizontal, $vertical, $locationPool);
        }

        if ($ionStormData->movementType === IonStormMovementType::VARIABLE) {
            $root->setData(json_encode($ionStormData->changeMovement($this->stuRandom), JSON_THROW_ON_ERROR));
            $this->anomalyRepository->save($root);
        }
    }

    private function moveChild(AnomalyInterface $child, int $horizontal, int $vertical, LocationPool $locationPool): void
    {
        $currentLocation = $child->getLocation();
        if ($currentLocation === null) {
            throw new RuntimeException('this should not happen');
        }

        $newLocation = $locationPool->getLocation(
            $currentLocation->getX() + $horizontal,
            $currentLocation->getY() + $vertical
        );

        if (
            $newLocation === null
            || $newLocation->hasAnomaly(AnomalyTypeEnum::ION_STORM)
            || $newLocation->isAnomalyForbidden()
        ) {
            $this->anomalyRepository->delete($child);
            return;
        }

        $currentLocation->getAnomalies()->removeElement($child);
        $child->setLocation($newLocation);
        $newLocation->addAnomaly($child);

        $this->anomalyRepository->save($child);
    }
}

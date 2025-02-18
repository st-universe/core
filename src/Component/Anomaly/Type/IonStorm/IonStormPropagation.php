<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use RuntimeException;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;

class IonStormPropagation
{
    public function __construct(
        private AnomalyRepositoryInterface $anomalyRepository,
        private AnomalyCreationInterface $anomalyCreation,
        private StuRandom $stuRandom
    ) {}

    public function propagateStorm(AnomalyInterface $root, LocationPool $locationPool): void
    {
        if ($root->getChildren()->isEmpty()) {
            $root->setRemainingTicks(0);
            return;
        }

        foreach ($root->getChildren()->toArray() as $child) {
            $this->transferRemainingTicks(
                $root,
                $child,
                $this->stuRandom->rand(1, 3)
            );

            $this->split($child, $root, $locationPool);
        }

        if (
            $root->getRemainingTicks() <= 1
            && $root->hasChildren()
        ) {
            $root->changeRemainingTicks(+1);
            $this->anomalyRepository->save($root);
        }
    }

    private function split(AnomalyInterface $anomaly, AnomalyInterface $root, LocationPool $locationPool): void
    {
        $remainingTicks = $anomaly->getRemainingTicks();
        if ($remainingTicks < 10) {
            return;
        }

        $location = $anomaly->getLocation();
        if ($location === null) {
            throw new RuntimeException('this should not happen');
        }

        $neighbours = $locationPool->getNeighbours($location);
        $location = $neighbours[array_rand($neighbours)];

        if ($location->isAnomalyForbidden()) {
            return;
        }

        $existingIonStorm = $location->getAnomaly(AnomalyTypeEnum::ION_STORM);
        if ($existingIonStorm === null) {

            $newChild = $this->anomalyCreation
                ->create(AnomalyTypeEnum::ION_STORM, $location, $root)
                ->setRemainingTicks(0);

            $this->anomalyRepository->save($newChild);

            $this->transferRemainingTicks(
                $anomaly,
                $newChild,
                $this->stuRandom->rand(10, 90)
            );
        } else {
            $this->transferRemainingTicks(
                $anomaly,
                $existingIonStorm,
                $this->stuRandom->rand(20, 70)
            );
        }
    }

    private function transferRemainingTicks(AnomalyInterface $source, AnomalyInterface $target, int $percentage): void
    {
        $transferAmount = (int)floor($source->getRemainingTicks() * $percentage / 100);
        if ($transferAmount !== 0) {
            $source->changeRemainingTicks(-$transferAmount);
            $target->changeRemainingTicks(+$transferAmount);

            $this->anomalyRepository->save($source);
            $this->anomalyRepository->save($target);
        }
    }
}

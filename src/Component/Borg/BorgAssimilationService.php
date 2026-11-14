<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;

final class BorgAssimilationService
{
    public function __construct(
        private BorgAssimilationRepositoryInterface $repository,
        private BorgAssimilationCapture $capture,
        private BorgAssimilationCompletion $completion,
        private BorgAssimilationRelease $release,
        private StuTime $time
    ) {}

    public function tryCapture(BorgSphereEvent $event, Ship $source): bool
    {
        return $this->capture->tryCapture($event, $source);
    }

    public function hasTargets(BorgSphereEvent $event, Ship $source): bool
    {
        return $this->capture->hasTargets($event, $source);
    }

    public function captureTarget(BorgSphereEvent $event, Ship $source, Ship $target): bool
    {
        return $this->capture->captureTarget($event, $source, $target);
    }

    public function canCaptureTarget(BorgSphereEvent $event, Ship $source, Ship $target): bool
    {
        return $this->capture->canCaptureTarget($event, $source, $target);
    }

    public function hasActiveCapture(Spacecraft $source): bool
    {
        return array_any($this->repository->getBySource($source), static fn (SpacecraftAssimilated $entry): bool => $entry->isActive());
    }

    public function process(): void
    {
        foreach ($this->repository->getActive() as $entry) {
            $source = $entry->getSource();
            if ($source !== null) {
                $this->repository->lock($source);
            }
            $this->repository->lock($entry->getSpacecraft());
            if (!$this->canContinue($entry)) {
                $this->release->release($entry, $this->time->time());
                continue;
            }
            $entry->setProgress($entry->getProgressAt($this->time->time()));
            if ($entry->getProgress() === 10000) {
                $this->completion->complete($entry, $this->time->time());
            }
            $this->repository->save($entry);
        }
    }

    private function canContinue(SpacecraftAssimilated $entry): bool
    {
        $source = $entry->getSource();
        $ship = $entry->getSpacecraft();
        return $source !== null && !$source->getCondition()->isDisabled() && !$source->getCondition()->isDestroyed()
            && !$ship->getCondition()->isDestroyed() && $source->getLocation() === $ship->getLocation()
            && $source->getTractoredShip() === $ship
            && $source->getSystemState(SpacecraftSystemTypeEnum::TRACTOR_BEAM)
            && $source->isSystemHealthy(SpacecraftSystemTypeEnum::TRACTOR_BEAM);
    }

    public function releaseForSource(Spacecraft $source, bool $rewardDataNode = true): void
    {
        foreach ($this->repository->getBySource($source) as $entry) {
            $now = $this->time->time();
            if ($this->completedBeforeRelease($entry, $now)) {
                $this->completion->complete($entry, $now);
            } else {
                $this->release->release($entry, $now, $rewardDataNode);
            }
        }
    }

    private function completedBeforeRelease(SpacecraftAssimilated $entry, int $now): bool
    {
        return $entry->isActive() && $entry->getProgressAt($now) === 10000
            && $entry->getSource()?->getCondition()->isDestroyed() === false
            && !$entry->getSpacecraft()->getCondition()->isDestroyed();
    }

    public function deleteForSpacecraft(Spacecraft $spacecraft, bool $rewardDataNodesForReleasedShips = false): void
    {
        foreach ($this->repository->getBySource($spacecraft) as $entry) {
            $this->release->release($entry, $this->time->time(), $rewardDataNodesForReleasedShips);
            $entry->setSource(null);
            $this->repository->save($entry);
        }
        $entry = $this->repository->getBySpacecraft($spacecraft);
        if ($entry !== null) {
            if ($entry->getSource()?->getTractoredShip() === $spacecraft) {
                $source = $entry->getSource();
                $source->setTractoredShip(null);
                $source->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
            $this->repository->delete($entry);
        }
    }

    public function deleteByRump(SpacecraftRump $rump): void
    {
        $this->repository->deleteByRump($rump);
    }
}

<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftNfsItem;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;

final class BorgAssimilationPresentation
{
    public function __construct(
        private BorgAssimilationRepositoryInterface $assimilations,
        private StuConfigInterface $config,
        private StuTime $time
    ) {}

    /** @return array{progress: float, active: bool, image: ?string, started: int, initial: int, duration: int, now: int, remaining: int} */
    public function getState(Spacecraft|SpacecraftNfsItem $target): array
    {
        $assimilation = $target instanceof Spacecraft ? $target->getAssimilation() : $this->assimilations->getBySpacecraftId($target->getId());
        $now = $this->time->time();
        $progress = (float) ($assimilation?->getProgressAt($now) ?? 0) / 100;
        $image = 'ships/assimilated/' . $target->getRumpId() . '.png';
        $hasImage = $assimilation !== null && is_file($this->config->getGameSettings()->getAssetsPath() . '/' . $image);
        $duration = $assimilation?->getDurationSeconds() ?? 900;
        $initial = $assimilation?->getInitialProgress() ?? 0;
        $started = $assimilation?->getStartedAt() ?? 0;
        $active = $assimilation?->isActive() ?? false;
        return [
            'progress' => $progress,
            'active' => $active,
            'image' => $hasImage ? '/assets/' . $image : null,
            'started' => $started,
            'initial' => $initial,
            'duration' => $duration,
            'now' => $now,
            'remaining' => $active ? max(0, $started + $duration - $now) : 0
        ];
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Research\SpecialResearchCompletion;
use Stu\Component\Research\SpecialResearchFacility;
use Stu\Component\Research\SpecialResearchFailure;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;

final class FinishSpecialResearchJobs implements ProcessTickHandlerInterface
{
    public function __construct(
        private SpecialResearchRepositoryInterface $repository,
        private StuRandom $random,
        private StuTime $time,
        private SpecialResearchCompletion $completion,
        private SpecialResearchFacility $facility,
        private SpecialResearchFailure $failure
    ) {}

    #[\Override]
    public function work(): void
    {
        $now = $this->time->time();
        foreach ($this->repository->getRunning() as $research) {
            if ($research->getStatus() !== SpecialResearchStatusEnum::RUNNING) {
                continue;
            }
            if (!$this->facility->isIntact($research)) {
                $this->completion->facilityLost($research, $now);
                continue;
            }
            if ($research->getFinishTime() > $now) {
                continue;
            }
            $failed = $this->random->rand(1, 100) <= $research->getFailureChance();
            $details = $failed ? $this->failure->apply($research) : '';
            $this->completion->finish($research, $failed, $details);
        }
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Component\Research\TechnologyBuildingLifecycle;
use Stu\Component\Research\TechnologyFacility;
use Stu\Component\Research\TechnologyProductionCompletion;
use Stu\Component\Research\TechnologyResearchCompletion;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class FinishTechnologyJobs implements ProcessTickHandlerInterface
{
    public function __construct(
        private TechnologyRepositoryInterface $repository,
        private TechnologyFacility $facility,
        private TechnologyBuildingLifecycle $lifecycle,
        private TechnologyResearchCompletion $completion,
        private TechnologyProductionCompletion $production,
        private StuRandom $random,
        private StuTime $time
    ) {}

    #[Override]
    public function work(): void
    {
        $now = $this->time->time();
        foreach ($this->repository->getRunningResearch() as $job) {
            if ($this->facility->getField($job) === null) {
                $this->lifecycle->abort($job);
            } elseif ($job->getFinishTime() <= $now) {
                $this->completion->finish($job, $this->random->rand(1, 10000) <= $job->getSuccessChance());
            }
        }
        $this->production->work(false);
    }
}

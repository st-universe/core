<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\TechnologyResearch;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class TechnologyResearchCompletion
{
    public function __construct(
        private TechnologyRepositoryInterface $repository,
        private ModuleOptimizationProgress $progress,
        private TechnologyFacility $facility,
        private SpecialResearchFailure $accident,
        private PrivateMessageSenderInterface $messages,
        private StuRandom $random,
        private StuTime $time
    ) {}

    public function finish(TechnologyResearch $job, bool $success): void
    {
        if ($job->getStatus() !== SpecialResearchStatusEnum::RUNNING) {
            return;
        }
        $details = $success ? $this->success($job) : $this->failure($job);
        $job->setStatus($success ? SpecialResearchStatusEnum::COMPLETED : SpecialResearchStatusEnum::FAILED)->setFinishTime($this->time->time());
        $this->repository->save($job);
        $this->messages->send(UserConstants::USER_NOONE, $job->getUser()->getId(), $details, PrivateMessageFolderTypeEnum::SPECIAL_COLONY);
    }

    private function success(TechnologyResearch $job): string
    {
        $module = $job->getModule();
        if ($job->getKind() === TechnologyRecipeKindEnum::MODULE_OPTIMIZATION && $module !== null) {
            $steps = $this->progress->improve($job->getUser(), $module, $job->getStartImprovementSteps(), $job->getMaxImprovementSteps());
            return sprintf('Die Optimierung von %s ist erfolgreich abgeschlossen. Dein Fertigungsbonus beträgt jetzt +%s %%', $module->getName(), number_format($steps / 10, 1, ',', ''));
        }
        return 'Die Nanitenfertigung wurde erfolgreich erforscht. Im Technologiecenter können jetzt eigene Borg-Naniten hergestellt werden';
    }

    private function failure(TechnologyResearch $job): string
    {
        $text = sprintf('%s ist fehlgeschlagen. Die eingesetzten Versuchsmaterialien wurden verbraucht', $job->getRecipe()->getName());
        if ($job->getAccidentChance() < 1 || $this->random->rand(1, 10000) > $job->getAccidentChance()) {
            return $text;
        }
        $field = $this->facility->getField($job);
        $colony = $job->getColony();
        if ($field === null || $colony === null || $job->getFailureSettings() === []) {
            return $text;
        }
        return $text . '. ' . $this->accident->applyAt($colony, $field, $job->getFailureSettings());
    }
}

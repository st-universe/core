<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class AllianceDeletionHandler implements PlayerDeletionHandlerInteface
{
    private AllianceJobRepositoryInterface $allianceJobRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->loggerUtil = $loggerUtil;
    }

    public function delete(UserInterface $user): void
    {
        $this->loggerUtil->log('stu', LoggerEnum::LEVEL_ERROR);

        foreach ($this->allianceJobRepository->getByUser($user->getId()) as $job) {
            $this->loggerUtil->log('A');
            if ($job->getType() === AllianceEnum::ALLIANCE_JOBS_FOUNDER) {
                $this->loggerUtil->log('B');
                $alliance = $job->getAlliance();

                $successor = $alliance->getSuccessor();

                if ($successor === null) {
                    $this->loggerUtil->log('C');
                    $this->allianceActionManager->delete($alliance->getId(), false);
                } else {
                    $this->loggerUtil->log('D');
                    $successorUserId = $successor->getUserId();

                    $this->allianceActionManager->setJobForUser(
                        $alliance->getId(),
                        $successorUserId,
                        AllianceEnum::ALLIANCE_JOBS_FOUNDER
                    );
                }
            }

            $this->allianceJobRepository->delete($job);
        }
    }
}

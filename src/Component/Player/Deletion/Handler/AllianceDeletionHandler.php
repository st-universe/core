<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class AllianceDeletionHandler implements PlayerDeletionHandlerInteface
{
    private $allianceJobRepository;

    private $allianceActionManager;

    public function __construct(
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager
    ) {
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
    }

    public function delete(UserInterface $user): void
    {
        foreach ($this->allianceJobRepository->getByUser($user->getId()) as $job) {
            if ($job->getType() === AllianceEnum::ALLIANCE_JOBS_FOUNDER) {
                $alliance = $job->getAlliance();

                if ($alliance->getSuccessor() === null) {
                    $this->allianceActionManager->delete($alliance->getId());
                } else {
                    $successorUserId = $alliance->getSuccessor()->getUserId();

                    $this->allianceJobRepository->truncateByUser($successorUserId);

                    $this->allianceActionManager->setJobForUser(
                        (int)$alliance->getId(),
                        $successorUserId,
                        AllianceEnum::ALLIANCE_JOBS_FOUNDER
                    );
                }
            }

            $this->allianceJobRepository->delete($job);
        }
    }
}

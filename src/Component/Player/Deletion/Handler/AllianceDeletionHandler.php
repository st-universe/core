<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class AllianceDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private AllianceJobRepositoryInterface $allianceJobRepository, private AllianceActionManagerInterface $allianceActionManager)
    {
    }

    #[Override]
    public function delete(UserInterface $user): void
    {
        foreach ($this->allianceJobRepository->getByUser($user->getId()) as $job) {
            if ($job->getType() === AllianceEnum::ALLIANCE_JOBS_FOUNDER) {
                $alliance = $job->getAlliance();

                $successor = $alliance->getSuccessor();

                if ($successor === null) {
                    $this->allianceJobRepository->delete($job);
                    $this->allianceActionManager->delete($alliance->getId(), false);
                } else {
                    $successorUserId = $successor->getUserId();

                    $this->allianceActionManager->setJobForUser(
                        $alliance->getId(),
                        $successorUserId,
                        AllianceEnum::ALLIANCE_JOBS_FOUNDER
                    );
                    $this->allianceJobRepository->delete($successor);
                }
            } else {
                $this->allianceJobRepository->delete($job);
            }
        }
    }
}

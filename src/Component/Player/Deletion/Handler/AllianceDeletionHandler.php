<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Doctrine\Common\Collections\Collection;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class AllianceDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private AllianceJobRepositoryInterface $allianceJobRepository, private AllianceActionManagerInterface $allianceActionManager) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        foreach ($this->allianceJobRepository->getByUser($user->getId()) as $job) {
            if ($job->getType() === AllianceEnum::ALLIANCE_JOBS_FOUNDER) {
                $alliance = $job->getAlliance();

                $successor = $alliance->getSuccessor();

                $diplomatic = $alliance->getDiplomatic();

                $members = $alliance->getMembers();
                $members->removeElement($user);

                $lastonlinemember = $this->getLastOnlineMember($members);

                if ($successor === null && $lastonlinemember === null) {
                    $this->allianceJobRepository->delete($job);
                    $this->allianceActionManager->delete($alliance->getId(), true);
                }
                if ($successor !== null) {
                    $successorUserId = $successor->getUserId();

                    $this->allianceActionManager->setJobForUser(
                        $alliance->getId(),
                        $successorUserId,
                        AllianceEnum::ALLIANCE_JOBS_FOUNDER
                    );
                    $this->allianceJobRepository->delete($successor);
                }
                if ($successor == null && $lastonlinemember != null) {
                    if ($diplomatic !== null) {
                        if ($lastonlinemember == $diplomatic->getUser()) {
                            $this->allianceJobRepository->delete($diplomatic);
                        }
                    }
                    $this->allianceActionManager->setJobForUser(
                        $alliance->getId(),
                        $lastonlinemember->getId(),
                        AllianceEnum::ALLIANCE_JOBS_FOUNDER
                    );
                }
            } else {
                $this->allianceJobRepository->delete($job);
            }
        }
    }

    /**
     * @param Collection<int, UserInterface> $members
     */
    private function getLastOnlineMember(Collection $members): ?UserInterface
    {
        $lastOnlineMember = null;
        $maxLastAction = 0;

        foreach ($members as $member) {
            if ($member->getLastAction() > $maxLastAction) {
                $maxLastAction = $member->getLastAction();
                $lastOnlineMember = $member;
            }
        }

        return $lastOnlineMember;
    }
}

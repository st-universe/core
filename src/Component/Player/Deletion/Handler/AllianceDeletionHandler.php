<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AllianceDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private AllianceActionManagerInterface $allianceActionManager,
        private UserRepositoryInterface $userRepository,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[Override]
    public function delete(User $user): void
    {
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            return;
        }

        $isFounder = $this->allianceJobManager->hasUserFounderPermission($user, $alliance);

        if ($isFounder) {
            $members = $alliance->getMembers();
            $members->removeElement($user);
            $user->setAlliance(null);
            $this->userRepository->save($user);

            $newFounder = $this->findNewFounder($alliance, $members);

            if ($newFounder === null) {
                $this->allianceJobManager->removeUserFromAllJobs($user, $alliance);
                $this->allianceActionManager->delete($alliance);
                return;
            }

            $founderJob = $alliance->getFounder();
            $this->allianceJobManager->removeUserFromJob($user, $founderJob);
            $this->allianceJobManager->removeUserFromAllJobs($newFounder, $alliance);
            $this->allianceJobManager->assignUserToJob($newFounder, $founderJob);
        } else {
            $this->allianceJobManager->removeUserFromAllJobs($user, $alliance);
        }
    }

    /**
     * @param Collection<int, User> $remainingMembers
     */
    private function findNewFounder(\Stu\Orm\Entity\Alliance $alliance, Collection $remainingMembers): ?User
    {
        if ($remainingMembers->isEmpty()) {
            return null;
        }

        $successorJob = $alliance->getSuccessor();
        if ($successorJob !== null) {
            $successors = $successorJob->getUsers();
            if (!empty($successors)) {
                return $this->getLastOnlineUser($successors);
            }
        }

        $diplomaticJob = $alliance->getDiplomatic();
        if ($diplomaticJob !== null) {
            $diplomats = $diplomaticJob->getUsers();
            if (!empty($diplomats)) {
                return $this->getLastOnlineUser($diplomats);
            }
        }

        return $this->getLastOnlineUser($remainingMembers->toArray());
    }

    /**
     * @param array<User> $users
     */
    private function getLastOnlineUser(array $users): ?User
    {
        if (empty($users)) {
            return null;
        }

        $lastOnlineUser = null;
        $maxLastAction = 0;

        foreach ($users as $user) {
            if ($user->getLastaction() > $maxLastAction) {
                $maxLastAction = $user->getLastaction();
                $lastOnlineUser = $user;
            }
        }

        return $lastOnlineUser;
    }
}

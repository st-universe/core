<?php

declare(strict_types=1);

namespace Stu\Component\Quest\AwayMission;

use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;
use Stu\Orm\Repository\ColonyProtectionRepositoryInterface;

final class AwayMissionDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private AwayMissionRepositoryInterface $awayMissionRepository,
        private ColonyProtectionRepositoryInterface $protectionRepository
    ) {}

    #[\Override]
    public function delete(User $user): void
    {
        $this->awayMissionRepository->deleteByUser($user);
        $this->awayMissionRepository->deleteByQuestOwner($user);
        $this->protectionRepository->deleteByQuestOwner($user);
    }

    public function deleteByColony(Colony $colony): void
    {
        $this->awayMissionRepository->deleteByColony($colony);
        $this->protectionRepository->deleteByColony($colony);
    }

    public function deleteByQuest(NPCQuest $quest): void
    {
        $this->awayMissionRepository->deleteByQuest($quest);
        $this->protectionRepository->deleteByQuest($quest);
    }
}

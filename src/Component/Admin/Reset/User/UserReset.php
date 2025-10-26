<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\User;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\UserSettingRepositoryInterface;

final class UserReset implements UserResetInterface
{
    public function __construct(
        private BlockedUserRepositoryInterface $blockedUserRepository,
        private UserRepositoryInterface $userRepository,
        private UserSettingRepositoryInterface $userSettingRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    #[\Override]
    public function archiveBlockedUsers(): void
    {
        echo "  - archive blocked users\n";

        foreach ($this->blockedUserRepository->findAll() as $blockedUser) {
            $blockedUser->setId($blockedUser->getId() + 10_000_000);
            $this->blockedUserRepository->save($blockedUser);
        }

        $this->entityManager->flush();
    }

    #[\Override]
    public function resetNpcs(): void
    {
        echo "  - reset NPCs\n";

        $time = time();

        foreach ($this->userRepository->getNpcList() as $npc) {
            $npc->setLastaction($time);
            $npc->setKnMark(0);
            $npc->setDescription('');
            $npc->setPrestige(0);
            $npc->setSessiondata('');
            $npc->getRegistration()->setPassword('');
            $npc->getRegistration()->setCreationDate($time);

            $this->userRepository->save($npc);

            $this->userSettingRepository->truncateByUser($npc);
        }

        $this->entityManager->flush();
    }
}

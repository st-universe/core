<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\User;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\NoteRepositoryInterface;
use Stu\Orm\Repository\PirateWrathRepositoryInterface;
use Stu\Orm\Repository\PrestigeLogRepositoryInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\UserSettingRepositoryInterface;

final class UserReset implements UserResetInterface
{
    public function __construct(
        private BlockedUserRepositoryInterface $blockedUserRepository,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private NoteRepositoryInterface $noteRepository,
        private PrestigeLogRepositoryInterface $prestigeLogRepository,
        private UserRepositoryInterface $userRepository,
        private UserSettingRepositoryInterface $userSettingRepository,
        private UserInvitationRepositoryInterface $userInvitationRepository,
        private UserIpTableRepositoryInterface $userIpTableRepository,
        private UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        private  PirateWrathRepositoryInterface $pirateWrathRepository,
        private EntityManagerInterface $entityManager
    ) {
        $this->blockedUserRepository = $blockedUserRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->noteRepository = $noteRepository;
        $this->prestigeLogRepository = $prestigeLogRepository;
        $this->userRepository = $userRepository;
        $this->userSettingRepository = $userSettingRepository;
        $this->userInvitationRepository = $userInvitationRepository;
        $this->userIpTableRepository = $userIpTableRepository;
        $this->userProfileVisitorRepository = $userProfileVisitorRepository;
        $this->entityManager = $entityManager;
    }

    public function archiveBlockedUsers(): void
    {
        echo "  - archive blocked users\n";

        foreach ($this->blockedUserRepository->findAll() as $blockedUser) {
            $blockedUser->setId($blockedUser->getId() + 10_000_000);
            $this->blockedUserRepository->save($blockedUser);
        }

        $this->entityManager->flush();
    }

    public function deleteAllDatabaseUserEntries(): void
    {
        echo "  - delete all database user entries\n";

        $this->databaseUserRepository->truncateAllEntries();

        $this->entityManager->flush();
    }

    public function deleteAllNotes(): void
    {
        echo "  - delete all notes\n";

        $this->noteRepository->truncateAllNotes();

        $this->entityManager->flush();
    }

    public function deleteAllPrestigeLogs(): void
    {
        echo "  - delete all prestige logs\n";

        $this->prestigeLogRepository->truncateAllPrestigeLogs();

        $this->entityManager->flush();
    }

    public function resetNpcs(): void
    {
        echo "  - reset NPCs\n";

        $time = time();

        foreach ($this->userRepository->getNpcList() as $npc) {
            $npc->setPassword('');
            $npc->setCreationDate($time);
            $npc->setLastaction($time);
            $npc->setKnMark(0);
            $npc->setDescription('');
            $npc->setPrestige(0);
            $npc->setSessiondata('');

            $this->userRepository->save($npc);

            $this->userSettingRepository->truncateByUser($npc);
        }

        $this->entityManager->flush();
    }

    public function deleteAllUserInvitations(): void
    {
        echo "  - delete all user invitations\n";

        $this->userInvitationRepository->truncateAllEntries();

        $this->entityManager->flush();
    }

    public function deleteAllUserIpTableEntries(): void
    {
        echo "  - delete all user ip table entries\n";

        $this->userIpTableRepository->truncateAllEntries();

        $this->entityManager->flush();
    }

    public function deleteAllUserProfileVisitors(): void
    {
        echo "  - delete all user ip table entries\n";

        $this->userProfileVisitorRepository->truncateAllEntries();

        $this->entityManager->flush();
    }

    public function deletePirateWrathEntries(): void
    {
        echo "  - delete all pirate wrath entries\n";

        $this->pirateWrathRepository->truncateAllEntries();

        $this->entityManager->flush();
    }
}

<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\User;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\NoteRepositoryInterface;
use Stu\Orm\Repository\PrestigeLogRepositoryInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class UserReset implements UserResetInterface
{
    private BlockedUserRepositoryInterface $blockedUserRepository;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private NoteRepositoryInterface $noteRepository;

    private PrestigeLogRepositoryInterface $prestigeLogRepository;

    private UserRepositoryInterface $userRepository;

    private UserInvitationRepositoryInterface $userInvitationRepository;

    private UserIpTableRepositoryInterface $userIpTableRepository;

    private UserProfileVisitorRepositoryInterface $userProfileVisitorRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        BlockedUserRepositoryInterface $blockedUserRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        NoteRepositoryInterface $noteRepository,
        PrestigeLogRepositoryInterface $prestigeLogRepository,
        UserRepositoryInterface $userRepository,
        UserInvitationRepositoryInterface $userInvitationRepository,
        UserIpTableRepositoryInterface $userIpTableRepository,
        UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->blockedUserRepository = $blockedUserRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->noteRepository = $noteRepository;
        $this->prestigeLogRepository = $prestigeLogRepository;
        $this->userRepository = $userRepository;
        $this->userInvitationRepository = $userInvitationRepository;
        $this->userIpTableRepository = $userIpTableRepository;
        $this->userProfileVisitorRepository = $userProfileVisitorRepository;
        $this->entityManager = $entityManager;
    }

    public function archiveBlockedUsers(): void
    {
        echo "  - archive blocked users\n";

        foreach ($this->blockedUserRepository->findAll() as $blockedUser) {
            $blockedUser->setId($blockedUser->getId() + 10000000);
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
            $npc->setRgbCode('');
            $npc->setPrestige(0);
            $npc->setStartPage(null);
            $npc->setSessiondata('');

            $this->userRepository->save($npc);
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
}

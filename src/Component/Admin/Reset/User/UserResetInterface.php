<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\User;

interface UserResetInterface
{
    public function archiveBlockedUsers(): void;

    public function deleteAllDatabaseUserEntries(): void;

    public function deleteAllNotes(): void;

    public function deleteAllPrestigeLogs(): void;

    public function resetNpcs(): void;

    public function deleteAllUserInvitations(): void;

    public function deleteAllUserIpTableEntries(): void;

    public function deleteAllUserProfileVisitors(): void;

    public function deletePirateWrathEntries(): void;
}

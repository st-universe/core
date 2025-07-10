<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\User;

interface UserResetInterface
{
    public function archiveBlockedUsers(): void;

    public function resetNpcs(): void;
}

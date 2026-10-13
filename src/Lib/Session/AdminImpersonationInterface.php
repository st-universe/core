<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Orm\Entity\User;

interface AdminImpersonationInterface
{
    public function isActive(): bool;

    public function getAdminId(): ?int;

    public function getTargetId(): ?int;

    public function resolveUser(User $admin, bool $adminContext): User;

    public function start(User $admin, User $target): void;

    public function stop(): void;
}

<?php

declare(strict_types=1);

namespace Stu\Module\Control;

interface GameUserRoleCheckerInterface
{
    public function isAdmin(): bool;

    public function isNpc(): bool;
}

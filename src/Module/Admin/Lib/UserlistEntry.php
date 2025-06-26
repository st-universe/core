<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Lib;

use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\User;

final class UserlistEntry
{
    public function __construct(private User $user) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserStateDescription(): string
    {
        if ($this->user->isLocked()) {
            return _('GESPERRT');
        }
        return UserEnum::getUserStateDescription($this->user->getState());
    }

    public function getUserStateColor(): string
    {
        $user = $this->user;
        if ($user->isLocked()) {
            return _("red");
        }
        if ($user->getState() === UserEnum::USER_STATE_ACTIVE) {
            return _("greenyellow");
        }
        return '#dddddd';
    }
}

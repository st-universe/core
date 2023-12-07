<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Lib;

use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;

final class UserlistEntry
{
    private UserInterface $user;

    public function __construct(
        UserInterface $user
    ) {
        $this->user = $user;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getUserStateDescription(): string
    {
        return $this->user->getUserStateDescription();
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

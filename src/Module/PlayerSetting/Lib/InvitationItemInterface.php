<?php

namespace Stu\Module\PlayerSetting\Lib;

use Stu\Orm\Entity\UserInterface;

interface InvitationItemInterface
{
    public function getLink(): string;

    public function getInvitedUser(): ?UserInterface;

    public function getDate(): int;

    public function isValid(): bool;
}

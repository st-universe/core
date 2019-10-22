<?php

namespace Stu\Component\Player\Invitation;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitationInterface;

interface InvitePlayerInterface
{
    public function invite(UserInterface $user): UserInvitationInterface;
}

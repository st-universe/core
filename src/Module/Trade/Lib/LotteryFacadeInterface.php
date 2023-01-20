<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\UserInterface;

interface LotteryFacadeInterface
{
    public function createLotteryTicket(UserInterface $user, bool $sendPm): void;
}

<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\LotteryTicket;
use Stu\Orm\Entity\User;

interface LotteryFacadeInterface
{
    public function createLotteryTicket(User $user, bool $sendPm): void;

    /**
     * Returns the ticket amount of either the current period or else the amount of the last period.
     */
    public function getTicketAmount(bool $isLastPeriod): int;

    /**
     * Returns the user ticket amount of either the current period or else the amount of the last period.
     */
    public function getTicketAmountByUser(int $userId, bool $isLastPeriod): int;

    /**
     * @return LotteryTicket[]
     */
    public function getTicketsOfLastPeriod(): array;
}

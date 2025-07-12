<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\LotteryTicketRepositoryInterface;

final class LotteryFacade implements LotteryFacadeInterface
{
    public function __construct(private LotteryTicketRepositoryInterface $lotteryTicketRepository, private PrivateMessageSenderInterface $privateMessageSender, private StuTime $stuTime) {}

    #[Override]
    public function createLotteryTicket(User $user, bool $sendPm): void
    {
        $ticket = $this->lotteryTicketRepository->prototype();
        $ticket->setUser($user);
        $ticket->setPeriod($this->getCurrentOrLastPeriod(false));
        $this->lotteryTicketRepository->save($ticket);

        if ($sendPm) {
            $this->privateMessageSender->send(
                UserConstants::USER_NPC_FERG,
                $user->getId(),
                'Du hast ein Gratislos für den aktuellen Lotteriezeitraum erhalten. Möge das Glück mit dir sein!',
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE
            );
        }
    }

    #[Override]
    public function getTicketAmount(bool $isLastPeriod): int
    {
        return $this->lotteryTicketRepository->getAmountByPeriod(
            $this->getCurrentOrLastPeriod($isLastPeriod)
        );
    }

    #[Override]
    public function getTicketAmountByUser(int $userId, bool $isLastPeriod): int
    {
        return $this->lotteryTicketRepository->getAmountByPeriodAndUser(
            $this->getCurrentOrLastPeriod($isLastPeriod),
            $userId
        );
    }

    #[Override]
    public function getTicketsOfLastPeriod(): array
    {
        return $this->lotteryTicketRepository->getByPeriod($this->getCurrentOrLastPeriod(true));
    }

    private function getCurrentOrLastPeriod(bool $isLastPeriod): string
    {
        $time = $this->stuTime->time();
        if ($isLastPeriod) {
            $time -= TimeConstants::ONE_DAY_IN_SECONDS;
        }

        return sprintf(
            '%d.%s',
            (int)date("Y", $time) + StuTime::STU_YEARS_IN_FUTURE_OFFSET,
            date("m", $time)
        );
    }
}

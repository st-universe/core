<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Component\Game\TimeConstants;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\LotteryTicketRepositoryInterface;

final class LotteryFacade implements LotteryFacadeInterface
{
    private LotteryTicketRepositoryInterface $lotteryTicketRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private StuTime $stuTime;

    public function __construct(
        LotteryTicketRepositoryInterface $lotteryTicketRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        StuTime $stuTime
    ) {
        $this->lotteryTicketRepository = $lotteryTicketRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->stuTime = $stuTime;
    }

    public function createLotteryTicket(UserInterface $user, bool $sendPm): void
    {
        $ticket = $this->lotteryTicketRepository->prototype();
        $ticket->setUser($user);
        $ticket->setPeriod($this->getCurrentOrLastPeriod(false));
        $this->lotteryTicketRepository->save($ticket);

        if ($sendPm) {
            $this->privateMessageSender->send(
                UserEnum::USER_NPC_FERG,
                $user->getId(),
                'Du hast ein Gratislos für den aktuellen Lotteriezeitraum erhalten. Möge das Glück mit dir sein!',
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE
            );
        }
    }

    public function getTicketAmount(bool $isLastPeriod): int
    {
        return $this->lotteryTicketRepository->getAmountByPeriod(
            $this->getCurrentOrLastPeriod($isLastPeriod)
        );
    }

    public function getTicketAmountByUser(int $userId, bool $isLastPeriod): int
    {
        return $this->lotteryTicketRepository->getAmountByPeriodAndUser(
            $this->getCurrentOrLastPeriod($isLastPeriod),
            $userId
        );
    }

    public function getTicketsOfLastPeriod(): array
    {
        return $this->lotteryTicketRepository->getByPeriod($this->getCurrentOrLastPeriod(true));
    }

    private function getCurrentOrLastPeriod(bool $isLastPeriod): string
    {
        $time = $this->stuTime->time();

        if ($isLastPeriod) {
            return date("Y.m", $time - TimeConstants::ONE_DAY_IN_SECONDS);
        } else {
            return date("Y.m", $time);
        }
    }
}

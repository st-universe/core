<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
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
        $ticket->setPeriod(date('Y.m', $this->stuTime->time()));
        $this->lotteryTicketRepository->save($ticket);

        if ($sendPm) {
            $this->privateMessageSender->send(
                UserEnum::USER_NPC_FERG,
                $user->getId(),
                'Du hast ein Gratislos für den aktuellen Lotteriezeitraum erhalten. Möge das Glück mit dir sein!',
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
            );
        }
    }
}

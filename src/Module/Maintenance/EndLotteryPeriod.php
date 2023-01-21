<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Trade\Lib\LotteryFacadeInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\LotteryTicketRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class EndLotteryPeriod implements MaintenanceHandlerInterface
{
    private LotteryTicketRepositoryInterface $lotteryTicketRepository;

    private TradePostRepositoryInterface $tradepostRepository;

    private LotteryFacadeInterface $lotteryFacade;

    private UserRepositoryInterface $userRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private PrivateMessageSenderInterface $privateMessageSender;

    private StuTime $stuTime;

    public function __construct(
        LotteryTicketRepositoryInterface $lotteryTicketRepository,
        TradePostRepositoryInterface $tradepostRepository,
        LotteryFacadeInterface $lotteryFacade,
        UserRepositoryInterface $userRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        PrivateMessageSenderInterface $privateMessageSender,
        StuTime $stuTime
    ) {
        $this->lotteryTicketRepository = $lotteryTicketRepository;
        $this->tradepostRepository = $tradepostRepository;
        $this->lotteryFacade = $lotteryFacade;
        $this->userRepository = $userRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->privateMessageSender = $privateMessageSender;
        $this->stuTime = $stuTime;
    }

    public function handle(): void
    {
        $time = $this->stuTime->time();
        $day = (int)date("j", $time);

        if ($day !== 1) {
            return;
        }

        $periodOfLastMonth = date("Y.m", $time - TimeConstants::ONE_DAY_IN_SECONDS);

        $tickets = $this->lotteryTicketRepository->getByPeriod($periodOfLastMonth);
        $ticketCount = count($tickets);

        if ($ticketCount === 0) {
            return;
        }

        $jackpot = (int)ceil($ticketCount / 100 * 80);

        $winnerIndex = rand(0, $ticketCount - 1);
        $losers = [];

        //set winner and loser
        for ($i = 0; $i < $ticketCount; $i++) {

            $ticket = $tickets[$i];
            $user = $ticket->getUser();

            if ($i === $winnerIndex) {
                $ticket->setIsWinner(true);
                $winner = $user;
            } else {
                $ticket->setIsWinner(false);
                $losers[$user->getId()] = $user;
            }

            $this->lotteryTicketRepository->save($ticket);
        }

        //jackpot to winner
        $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);
        $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $user);

        $storageManagerUser->upperStorage(
            CommodityTypeEnum::COMMODITY_LATINUM,
            $jackpot
        );

        //PM to winner
        $this->privateMessageSender->send(
            GameEnum::USER_FERG_NPC,
            $winner->getId(),
            sprintf(
                "Du hast %d Latinum in der Lotterie gewonnen.\nEs waren %d Lose im Topf.\nDer Gewinn wartet auf dich am Handelsposten 'Zur goldenen Kugel'",
                $jackpot,
                $ticketCount
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
        );

        //PM to all losers
        foreach ($losers as $loserId => $user) {
            $this->privateMessageSender->send(
                GameEnum::USER_FERG_NPC,
                $loserId,
                sprintf(
                    "%s hat %d Latinum in der Lotterie gewonnen.\nEs waren %d Lose im Topf.\nViel Glück beim nächsten Mal!",
                    $winner->getName(),
                    $jackpot,
                    $ticketCount
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
            );
        }

        $userCount = $this->userRepository->getActiveAmount();

        //give random users a ticket
        foreach ($this->userRepository->getNonNpcList() as $user) {

            $winRateInPercent = 10 * $user->getId() / $userCount;

            if (rand(1, 100) > $winRateInPercent) {
                continue;
            }

            $this->lotteryFacade->createLotteryTicket($user, true);
        }
    }
}

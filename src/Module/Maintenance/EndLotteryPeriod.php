<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Player\AwardTypeEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Trade\Lib\LotteryFacadeInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AwardRepositoryInterface;
use Stu\Orm\Repository\LotteryTicketRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class EndLotteryPeriod implements MaintenanceHandlerInterface
{
    private LotteryTicketRepositoryInterface $lotteryTicketRepository;

    private TradePostRepositoryInterface $tradepostRepository;

    private AwardRepositoryInterface $awardRepository;

    private LotteryFacadeInterface $lotteryFacade;

    private UserRepositoryInterface $userRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private CreateUserAwardInterface $createUserAward;

    private CreatePrestigeLogInterface $createPrestigeLog;

    private PrivateMessageSenderInterface $privateMessageSender;

    private StuTime $stuTime;

    public function __construct(
        LotteryTicketRepositoryInterface $lotteryTicketRepository,
        TradePostRepositoryInterface $tradepostRepository,
        AwardRepositoryInterface $awardRepository,
        LotteryFacadeInterface $lotteryFacade,
        UserRepositoryInterface $userRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        CreateUserAwardInterface $createUserAward,
        CreatePrestigeLogInterface $createPrestigeLog,
        PrivateMessageSenderInterface $privateMessageSender,
        StuTime $stuTime
    ) {
        $this->lotteryTicketRepository = $lotteryTicketRepository;
        $this->tradepostRepository = $tradepostRepository;
        $this->awardRepository = $awardRepository;
        $this->lotteryFacade = $lotteryFacade;
        $this->userRepository = $userRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->createUserAward = $createUserAward;
        $this->createPrestigeLog = $createPrestigeLog;
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

        $tickets = $this->lotteryFacade->getTicketsOfLastPeriod();
        $ticketCount = count($tickets);

        if ($ticketCount === 0) {
            return;
        }

        $jackpot = (int)ceil($ticketCount / 100 * 80);

        $winnerIndex = random_int(0, $ticketCount - 1);
        $losers = [];
        $winner = null;

        //set winner and loser
        for ($i = 0; $i < $ticketCount; $i++) {
            $ticket = $tickets[$i];
            $user = $ticket->getUser();

            if ($i === $winnerIndex) {
                $ticket->setIsWinner(true);
                $winner = $user;

                $this->createAwardAndPrestige($winner, $time);

                //jackpot to winner
                $this->payOutLatinum($winner, $jackpot);
            } else {
                $ticket->setIsWinner(false);

                $losers[$user->getId()] = $user;
            }

            $this->lotteryTicketRepository->save($ticket);
        }

        if ($winner === null) {
            return;
        }

        //PM to winner
        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
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
            //skip winner if he had more than one ticket
            if ($user === $winner) {
                continue;
            }

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
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
            $winRateInPercent = 10 * ($user->getId() - UserEnum::USER_FIRST_ID) / $userCount;

            if (random_int(1, 100) > $winRateInPercent) {
                continue;
            }

            $this->lotteryFacade->createLotteryTicket($user, true);
        }
    }

    private function createAwardAndPrestige(UserInterface $user, int $time): void
    {
        $award = $this->awardRepository->find(AwardTypeEnum::LOTTERY_WINNER->value);

        if ($award !== null) {
            $this->createUserAward->createAwardForUser(
                $user,
                $award
            );
        }

        $amount = $this->lotteryFacade->getTicketAmountByUser($user->getId(), true);

        $this->createPrestigeLog->createLog(
            $amount,
            sprintf('%1$d Prestige erhalten für den Erwerb von %1$d Losen in der letzten Lotterieziehung', $amount),
            $user,
            $time
        );
    }

    private function payOutLatinum(UserInterface $winner, int $jackpot): void
    {
        $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);
        $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $winner);

        $storageManagerUser->upperStorage(
            CommodityTypeEnum::COMMODITY_LATINUM,
            $jackpot
        );
    }
}

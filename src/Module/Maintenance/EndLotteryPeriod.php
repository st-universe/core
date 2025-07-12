<?php

namespace Stu\Module\Maintenance;

use Override;
use RuntimeException;
use Stu\Component\Player\UserAwardEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Trade\Lib\LotteryFacadeInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\LotteryWinnerBuildplan;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AwardRepositoryInterface;
use Stu\Orm\Repository\LotteryTicketRepositoryInterface;
use Stu\Orm\Repository\LotteryWinnerBuildplanRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class EndLotteryPeriod implements MaintenanceHandlerInterface
{
    public function __construct(
        private LotteryTicketRepositoryInterface $lotteryTicketRepository,
        private TradePostRepositoryInterface $tradepostRepository,
        private AwardRepositoryInterface $awardRepository,
        private LotteryWinnerBuildplanRepositoryInterface $lotteryWinnerBuildplanRepository,
        private UserRepositoryInterface $userRepository,
        private LotteryFacadeInterface $lotteryFacade,
        private TradeLibFactoryInterface $tradeLibFactory,
        private CreateUserAwardInterface $createUserAward,
        private ShipCreatorInterface $shipCreator,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StuTime $stuTime,
        private StuRandom $stuRandom
    ) {}

    #[Override]
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

        $tradePost = $this->tradepostRepository->find(TradeEnum::DEALS_FERG_TRADEPOST_ID);
        if ($tradePost === null) {
            throw new RuntimeException('no deals ferg tradepost found');
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

                //jackpot to winner
                $this->createAwardAndPrestige($winner, $time);
                $this->payOutLatinum($winner, $jackpot, $ticketCount, $tradePost);
                $this->transmitShip($winner, $tradePost);
            } else {
                $ticket->setIsWinner(false);

                $losers[$user->getId()] = $user;
            }

            $this->lotteryTicketRepository->save($ticket);
        }

        if ($winner === null) {
            return;
        }

        //PM to all losers
        foreach ($losers as $loserId => $user) {
            //skip winner if he had more than one ticket
            if ($user === $winner) {
                continue;
            }

            $this->privateMessageSender->send(
                $tradePost->getUserId(),
                $loserId,
                sprintf(
                    "%s hat %d Latinum in der Lotterie gewonnen.\nEs waren %d Lose im Topf.\nViel Glück beim nächsten Mal!",
                    $winner->getName(),
                    $jackpot,
                    $ticketCount
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE
            );
        }

        $userCount = $this->userRepository->getActiveAmount();

        //give random users a ticket
        foreach ($this->userRepository->getNonNpcList() as $user) {
            $winRateInPercent = 10 * ($user->getId() - UserConstants::USER_FIRST_ID) / $userCount;

            if (random_int(1, 100) > $winRateInPercent) {
                continue;
            }

            $this->lotteryFacade->createLotteryTicket($user, true);
        }
    }

    private function createAwardAndPrestige(User $user, int $time): void
    {
        $award = $this->awardRepository->find(UserAwardEnum::LOTTERY_WINNER);

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

    private function payOutLatinum(User $winner, int $jackpot, int $ticketCount, TradePost $tradePost): void
    {
        $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $winner);

        $storageManagerUser->upperStorage(
            CommodityTypeConstants::COMMODITY_LATINUM,
            $jackpot
        );

        $this->privateMessageSender->send(
            $tradePost->getUserId(),
            $winner->getId(),
            sprintf(
                "Du hast %d Latinum in der Lotterie gewonnen.\nEs waren %d Lose im Topf.\nDer Gewinn wartet auf dich am Handelsposten 'Zur goldenen Kugel'",
                $jackpot,
                $ticketCount
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE
        );
    }

    private function transmitShip(User $winner, TradePost $tradePost): void
    {
        /** @var array<int, LotteryWinnerBuildplan> */
        $winnerBuildplans = $this->lotteryWinnerBuildplanRepository->findByFactionId($winner->getFactionId());
        if (empty($winnerBuildplans)) {
            return;
        }

        $chances = array_map(fn(LotteryWinnerBuildplan $winnerBuildplan): int => $winnerBuildplan->getChance(), $winnerBuildplans);

        $randomKey = $this->stuRandom->randomKeyOfProbabilities($chances);
        $buildplan = $winnerBuildplans[$randomKey]->getBuildplan();

        $wrapper = $this->shipCreator
            ->createBy(
                $winner->getId(),
                $buildplan->getRumpId(),
                $buildplan->getId()
            )->setLocation($tradePost->getStation()->getLocation())
            ->setSpacecraftName(sprintf(
                'Lotteriegewinn (%s)',
                $this->stuTime->transformToStuDate(time())
            ))
            ->finishConfiguration();

        $ship = $wrapper->get();

        $this->privateMessageSender->send(
            $tradePost->getUserId(),
            $winner->getId(),
            sprintf(
                "Als zusätzlicher Lotteriegewinn wurde dir ein Schiff der %s-Klasse zu den Koordinaten %s am Handelsposten 'Zur goldenen Kugel' überstellt.",
                $ship->getRumpName(),
                $ship->getSectorString()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }
}

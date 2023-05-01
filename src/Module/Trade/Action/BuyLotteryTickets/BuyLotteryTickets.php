<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\BuyLotteryTickets;

use request;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\LotteryFacadeInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowLottery\ShowLottery;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class BuyLotteryTickets implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUY_LOTTERY_TICKETS';

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradePostRepositoryInterface $tradepostRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private LotteryFacadeInterface $lotteryFacade;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradePostRepositoryInterface $tradepostRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        LotteryFacadeInterface $lotteryFacade,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradepostRepository = $tradepostRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->lotteryFacade = $lotteryFacade;
        $this->storageRepository = $storageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $game->setView(ShowLottery::VIEW_IDENTIFIER);

        if (!$this->tradeLicenseRepository->hasFergLicense($userId)) {
            $game->addInformation(_('Um Lotterielose zu kaufen wird eine Handelslizenz bei der goldenen Kugel benötigt'));
            return;
        }

        if ($userId < 100) {
            $game->addInformation(_('NPCs können keine Lose kaufen'));
            return;
        }

        $amount = request::postIntFatal('amount');

        if ($amount < 0) {
            return;
        }

        $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
            TradeEnum::DEALS_FERG_TRADEPOST_ID,
            $userId,
            CommodityTypeEnum::COMMODITY_LATINUM
        );

        if ($storage === null || $storage->getAmount() < $amount) {
            $game->addInformation(_('Es befindet sich nicht genügend Latinum auf diesem Handelsposten'));
            return;
        }

        $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);
        $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $user);

        $storageManagerUser->lowerStorage(
            CommodityTypeEnum::COMMODITY_LATINUM,
            $amount
        );

        //buy tickets
        for ($i = 0; $i < $amount; $i++) {
            $this->lotteryFacade->createLotteryTicket($user, false);
        }

        $game->addInformationf(_('%d Lotterielos(e) wurde gekauft'), $amount);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

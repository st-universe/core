<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsBidAuction;

use Stu\Exception\AccessViolation;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\DealsAuctionRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class DealsBidAuction implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEALS_BID_AUCTION';

    private DealsBidAuctionRequestInterface $dealsbidauctionRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private DealsRepositoryInterface $dealsRepository;

    private DealsAuctionRepositoryInterface $dealsAuctionRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TradePostRepositoryInterface $tradepostRepository;

    private StorageRepositoryInterface $storageRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    public function __construct(
        DealsBidAuctionRequestInterface $dealsbidauctionRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        DealsRepositoryInterface $dealsRepository,
        DealsAuctionRepositoryInterface $dealsAuctionRepository,
        TradePostRepositoryInterface $tradepostRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeTransactionRepositoryInterface $tradeTransactionRepository,
        StorageRepositoryInterface $storageRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        CreatePrestigeLogInterface $createPrestigeLog
    ) {
        $this->dealsbidauctionRequest = $dealsbidauctionRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradepostRepository = $tradepostRepository;
        $this->dealsRepository = $dealsRepository;
        $this->dealsAuctionRepository = $dealsAuctionRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeTransactionRepository = $tradeTransactionRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->storageRepository = $storageRepository;
        $this->createPrestigeLog = $createPrestigeLog;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $dealId = $this->dealsbidauctionRequest->getDealId();
        $amount = $this->dealsbidauctionRequest->getAmount();
        $maxamount = $this->dealsbidauctionRequest->getMaxAmount();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $selectedDeal = $this->dealsRepository->find($dealId);
        $selectedAuction = $selectedDeal->getAuctions();
        $actualmaxamount = $selectedDeal->getAuctions()->getMaxAmount();

        if ($amount < 1) {
            $game->addInformation(_('Zu geringe Anzahl ausgewählt'));
            return;
        }

        if ($selectedDeal === null) {
            $game->addInformation(_('Das Angebot ist nicht mehr verfügbar'));
            return;
        }

        if (!$this->dealsRepository->getFergLicense($userId)) {
            throw new AccessViolation(sprintf(
                _('UserId %d does not have license for Deals'),
                $userId
            ));
        }
        if ($maxamount < $amount) {
            $maxamount = $amount;
        }

        if ($actualmaxamount >= $amount && $actualmaxamount >= $maxamount) {

            $selectedAuction->setActualAmount((int) $maxamount + 1);
            $this->dealsAuctionRepository->save($selectedAuction);

            $game->addInformation(sprintf(_('Dein Maximalgebot hat nicht ausgereicht. Höchstgebot liegt bei %d'), $maxamount + 1));

            $this->privateMessageSender->send(
                14,
                $selectedAuction->getUserId(),
                sprintf(
                    'Ein Spieler hat auf ein Angebot beim Deals des Großen Nagus geboten, aber dein Maximalgebot nicht überschritten. Dein Höchstgebot liet nun bei %s',
                    $maxamount + 1
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
            );
        }



        if ($maxamount > $actualmaxamount) {
            if ($amount <= $actualmaxamount) {
                $amount = $actualmaxamount + 1;
            }

            if ($selectedDeal->getwantCommodityId() !== null) {
                $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
                    TradeEnum::DEALS_FERG_TRADEPOST_ID,
                    $userId,
                    $selectedDeal->getWantCommodityId()
                );


                if ($storage === null || $storage->getAmount() < $amount) {
                    $game->addInformation(sprintf(
                        _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                        $selectedDeal->getWantedCommodity()->getName()
                    ));
                    return;
                }
            }

            $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);

            $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $userId);
            $storageManagerSecondUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $selectedDeal->getAuctionUser()->getId());


            if ($selectedDeal->getwantCommodityId() !== null) {

                if ($amount  > $storage->getAmount()) {
                    $amount = (int) floor($storage->getAmount() / $selectedDeal->getwantCommodityAmount());
                }
            }

            if ($selectedDeal->getwantPrestige() !== null) {
                $userprestige = $game->getUser()->getPrestige();

                if ($amount * $selectedDeal->getwantPrestige() > $userprestige) {
                    $amount = (int) floor($userprestige / $selectedDeal->getwantPrestige());
                }
            }


            if ($selectedDeal->getwantCommodityId() !== null) {
                $storageManagerUser->lowerStorage(
                    (int) $selectedDeal->getwantCommodityId(),
                    (int) $amount
                );
                if ($selectedDeal->getAuctionUserId() > 100) {
                    $storageManagerSecondUser->upperStorage(
                        $selectedDeal->getwantCommodityId(),
                        $actualmaxamount
                    );

                    $this->privateMessageSender->send(
                        14,
                        $selectedDeal->getAuctionUserId(),
                        sprintf(
                            'Du wurdes bei einer Auktion des großen Nagus von %s überboten und hast %d %s zurück bekommen. Das aktuelle Gebot liegt bei: %d %s',
                            $user->getUserName(),
                            $actualmaxamount,
                            $selectedDeal->getWantedCommodity()->getName(),
                            $amount,
                            $selectedDeal->getWantedCommodity()->getName()
                        ),
                        PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
                    );
                }
            }

            if ($selectedDeal->getwantPrestige() !== null) {
                $description = sprintf(
                    '-%d Prestige: Eingebüßt bei einer Auktion des Großen Nagus',
                    $amount * $selectedDeal->getwantPrestige()
                );
                $this->createPrestigeLog->createLog(- ($amount * $selectedDeal->getwantPrestige()), $description, $game->getUser(), time());

                if ($selectedDeal->getAuctionUserId() > 100) {
                    $descriptionsecond = sprintf(
                        '%d Prestige: Du wurdest bei einer Auktion des Großen Nagus überboten und hast dein Prestige zurück erhalten',
                        $amount * $selectedDeal->getwantPrestige()
                    );
                    $this->createPrestigeLog->createLog($actualmaxamount, $descriptionsecond, $selectedDeal->getAuctionUser(), time());

                    $this->privateMessageSender->send(
                        14,
                        $selectedDeal->getAuctionUserId(),
                        sprintf(
                            'Du wurdest bei einer Auktion des großen Nagus von %s überboten und hast %d Prestige zurück bekommen. Das aktuelle Gebot liegt bei: %d Prestige',
                            $user->getUserName(),
                            $actualmaxamount,
                            $amount

                        ),
                        PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
                    );
                }

                $selectedAuction->setActualAmount((int) $amount);
                $selectedAuction->setAuctionUser($user);
                $selectedAuction->setMaxAmount((int) $amount);
                $this->dealsAuctionRepository->save($selectedAuction);

                $game->addInformation(sprintf(_('Gebot wurde auf %d erhöht. Du bist nun meistbietender!'), $amount));
            }
        }
    }



    public function performSessionCheck(): bool
    {
        return true;
    }
}
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
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class DealsBidAuction implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEALS_BID_AUCTION';

    private DealsBidAuctionRequestInterface $dealsbidauctionRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private DealsRepositoryInterface $dealsRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TradePostRepositoryInterface $tradepostRepository;

    private StorageRepositoryInterface $storageRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    public function __construct(
        DealsBidAuctionRequestInterface $dealsbidauctionRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        DealsRepositoryInterface $dealsRepository,
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
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $selectedDeal = $this->dealsRepository->find($dealId);

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

        if ($selectedDeal->getwantCommodityId() !== null) {
            $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
                TradeEnum::DEALS_FERG_TRADEPOST_ID,
                $userId,
                $selectedDeal->getWantCommodityId()
            );


            if ($storage === null || $storage->getAmount() < $selectedDeal->getwantCommodityAmount()) {
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

        $freeStorage = $storageManagerUser->getFreeStorage();

        if ($selectedDeal->getwantCommodityId() !== null) {

            if ($amount * $selectedDeal->getwantCommodityAmount() > $storage->getAmount()) {
                $amount = (int) floor($storage->getAmount() / $selectedDeal->getwantCommodityAmount());
            }
        }

        if ($selectedDeal->getwantPrestige() !== null) {
            $userprestige = $game->getUser()->getPrestige();

            if ($amount * $selectedDeal->getwantPrestige() > $userprestige) {
                $amount = (int) floor($userprestige / $selectedDeal->getwantPrestige());
            }
        }
        if ($selectedDeal->getAuctionAmount() >= $amount) {
            $game->addInformation(_('Zu geringe Menge angegeben'));
            return;
        }

        if ($selectedDeal->getwantCommodityId() !== null) {
            $storageManagerUser->lowerStorage(
                (int) $selectedDeal->getwantCommodityId(),
                (int) $selectedDeal->getwantCommodityAmount() * $amount
            );

            $storageManagerSecondUser->upperStorage(
                (int) $selectedDeal->getwantCommodityId(),
                (int) $selectedDeal->getwantCommodityAmount() * $selectedDeal->getAuctionAmount()
            );

            $this->privateMessageSender->send(
                $selectedDeal->getAuctionUserId(),
                $selectedDeal->getAuctionUserId(),
                sprintf(
                    'Du wurdes bei einer Auction des großen Nagus von %s überboten und hast insgesamt %d %s zurück bekommen. Das aktuelle Gebot liegt bei: %d %s',
                    $user->getUserName(),
                    $selectedDeal->getAuctionAmount(),
                    $selectedDeal->getWantedCommodity()->getName(),
                    $amount,
                    $selectedDeal->getWantedCommodity()->getName()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
            );
        }

        if ($selectedDeal->getwantPrestige() !== null) {
            $description = sprintf(
                '-%d Prestige: Eingebüßt bei einer Auction des Großen Nagus',
                $amount * $selectedDeal->getwantPrestige()
            );
            $this->createPrestigeLog->createLog(- ($amount * $selectedDeal->getwantPrestige()), $description, $game->getUser(), time());
            if ($selectedDeal->getAuctionUserId() > 100) {
                $descriptionsecond = sprintf(
                    '%d Prestige: Du wurdest bei einer Auction des Großen Nagus überboten und has dein Prestige zurück erhalten',
                    $amount * $selectedDeal->getwantPrestige()
                );
                $this->createPrestigeLog->createLog($selectedDeal->getAuctionAmount(), $descriptionsecond, $selectedDeal->getAuctionUser(), time());

                $this->privateMessageSender->send(
                    $selectedDeal->getAuctionUserId(),
                    $selectedDeal->getAuctionUserId(),
                    sprintf(
                        'Du wurdes bei einer Auction des großen Nagus von %s überboten und hast insgesamt %d Prestige zurück bekommen. Das aktuelle Gebot liegt bei: %d Prestige',
                        $user->getUserName(),
                        $selectedDeal->getAuctionAmount(),
                        $amount

                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
                );
            }



            if ($selectedDeal->getAmount() !== null) {

                $selectedDeal->setAuctionAmount((int) $amount);
                $selectedDeal->setAuctionUserId((int)$userId);
                $this->dealsRepository->save($selectedDeal);
            }

            $game->addInformation(sprintf(_('Gebot wurde auf %d erhöht. Du bist nun meistbietender!'), $amount));
        }
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
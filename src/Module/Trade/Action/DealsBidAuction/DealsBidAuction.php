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
use Stu\Orm\Entity\AuctionBidInterface;
use Stu\Orm\Entity\DealsInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;
use Stu\Orm\Repository\AuctionBidRepositoryInterface;

final class DealsBidAuction implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEALS_BID_AUCTION';

    private DealsBidAuctionRequestInterface $dealsbidauctionRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private DealsRepositoryInterface $dealsRepository;

    private AuctionBidRepositoryInterface $auctionBidRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TradePostRepositoryInterface $tradepostRepository;

    private StorageRepositoryInterface $storageRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    public function __construct(
        DealsBidAuctionRequestInterface $dealsbidauctionRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        AuctionBidRepositoryInterface $auctionBidRepository,
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
        $this->auctionBidRepository = $auctionBidRepository;
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
        $maxAmount = $this->dealsbidauctionRequest->getMaxAmount();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $auction = $this->dealsRepository->find($dealId);
        $currentBidAmount = $auction->getAuctionAmount();


        if ($maxAmount < 1 || $maxAmount <= $currentBidAmount) {
            $game->addInformation(_('Zu geringe Anzahl ausgewählt'));
            return;
        }

        if ($auction === null) {
            $game->addInformation(_('Die Auktion ist nicht mehr verfügbar'));
            return;
        }

        if (!$this->dealsRepository->getFergLicense($userId)) {
            throw new AccessViolation(sprintf(
                _('UserId %d does not have license for Deals'),
                $userId
            ));
        }

        $highestBid = $auction->getHighestBid();
        if ($highestBid === null) {
            $this->createFirstBid($maxAmount, $auction, $game, $userId);
            return;
        }

        $userHasHighestBid = $highestBid->getUser() === $user;
        if ($userHasHighestBid) {
            $this->raiseOwnBit($maxAmount, $highestBid, $game, $auction);
            return;
        }

        $currentMaxAmount = $highestBid->getMaxAmount();

        if ($maxAmount <= $currentMaxAmount) {
            $this->raiseCurrentAmount($maxAmount, $currentMaxAmount, $auction, $game);
        }

        if ($maxAmount > $currentMaxAmount) {
            $this->setNewHighestBid($maxAmount, $auction, $game);
        }
    }

    private function createFirstBid(int $maxAmount, DealsInterface $auction, GameControllerInterface $game, $userId): void
    {
        if ($auction->getwantCommodityId() !== null) {
            $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);
            $storageManagerNew = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $userId);

            $storageManagerNew->lowerStorage(
                $auction->getwantCommodityId(),
                ($maxAmount)
            );
        }

        if ($auction->getwantPrestige() !== null) {
            $description = sprintf(
                '-%d Prestige: Eingebüßt beim setzen eines Erstgebots bei einer Auktion des Großen Nagus',
                ($maxAmount)
            );

            $this->createPrestigeLog->createLog($maxAmount, $description, $game->getUser(), time());
        }

        $bid = $this->auctionBidRepository->prototype();
        $bid->setUser($game->getUser());
        $bid->setMaxAmount($maxAmount);
        $bid->setAuction($auction);
        $this->auctionBidRepository->save($bid);

        $auction->setAuctionAmount(1);
        $this->dealsRepository->save($auction);

        $game->addInformation(sprintf(_('Du hast das erste Gebot abgegeben. Dein Maximalgebot liegt bei %d'), $maxAmount));
    }

    private function raiseOwnBit(int $maxAmount, AuctionBidInterface $bid, GameControllerInterface $game, $auction): void
    {

        $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);

        $currentHighestBid = $auction->getHighestBid();
        $storageManagerOld = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $currentHighestBid->getUser()->getId());



        if ($auction->getwantCommodityId() !== null) {
            $storageManagerOld->lowerStorage(
                $auction->getwantCommodityId(),
                ($maxAmount - $currentHighestBid->getMaxAmount())
            );
        }


        if ($auction->getwantPrestige() !== null) {
            $description = sprintf(
                '-%d Prestige: Eingebüßt beim erhöhen deines Maximalgebots bei einer Auktion des Großen Nagus',
                ($maxAmount - $currentHighestBid->getMaxAmount())
            );

            $this->createPrestigeLog->createLog($maxAmount - $currentHighestBid->getMaxAmount(), $description, $game->getUser(), time());
        }
        $game->addInformation(sprintf(_('Dein Maximalgebot wurde auf %d erhöht'), $maxAmount));
        $bid->setMaxAmount($maxAmount);
        $this->auctionBidRepository->save($bid);
    }

    private function raiseCurrentAmount(int $maxAmount, int $currentMaxAmount, DealsInterface $auction, $game): void
    {
        if ($maxAmount < $currentMaxAmount) {
            $auction->setAuctionAmount($maxAmount + 1);
            $this->dealsRepository->save($auction);
        }

        if ($maxAmount = $currentMaxAmount) {
            $auction->setAuctionAmount($currentMaxAmount);
            $this->dealsRepository->save($auction);
        }

        $game->addInformation(sprintf(_('Dein Maximalgebot hat nicht ausgereicht. Höchstgebot liegt nun bei %d'), $currentMaxAmount));

        if ($auction->getwantCommodityId() !== null) {

            $this->privateMessageSender->send(
                14,
                $auction->getHighestBid()->getUserId(),
                sprintf(
                    'Ein Spieler hat auf ein Angebot bei "Deals des Großen Nagus" geboten, aber dein Maximalgebot nicht überschritten. Dein Höchstgebot liegt nun bei %d %s',
                    $auction->getAuctionAmount(),
                    $auction->getWantedCommodity()->getName()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
            );
        }

        if ($auction->getwantPrestige() !== null) {

            $this->privateMessageSender->send(
                14,
                $auction->getHighestBid()->getUserId(),
                sprintf(
                    'Ein Spieler hat auf ein Angebot bei "Deals des Großen Nagus" geboten, aber dein Maximalgebot nicht überschritten. Dein Höchstgebot liegt nun bei %d Prestige',
                    $auction->getAuctionAmount(),
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
            );
        }
    }


    private function setNewHighestBid(int $maxAmount, DealsInterface $auction, GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $currentHighestBid = $auction->getHighestBid();
        $newCurrentAmount = $currentHighestBid->getMaxAmount() + 1;

        if ($auction->getwantCommodityId() !== null) {
            $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
                TradeEnum::DEALS_FERG_TRADEPOST_ID,
                $userId,
                $auction->getWantCommodityId()
            );

            if ($storage === null || $storage->getAmount() < $newCurrentAmount) {
                $game->addInformation(sprintf(
                    _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                    $auction->getWantedCommodity()->getName()
                ));
                return;
            }
        }

        if ($auction->getwantPrestige() !== null) {
            $userprestige = $game->getUser()->getPrestige();

            if ($newCurrentAmount > $userprestige) {
                $game->addInformation(sprintf(
                    _('Du hast nicht genügend Prestige, benötigt: %d'),
                    $newCurrentAmount
                ));
                return;
            }
        }

        $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);

        $storageManagerNew = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $userId);
        $storageManagerOld = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $currentHighestBid->getUser()->getId());

        if ($auction->getwantCommodityId() !== null) {
            $storageManagerNew->lowerStorage(
                $auction->getwantCommodityId(),
                $maxAmount
            );

            if ($currentHighestBid->getUserId() > 100) {
                $storageManagerOld->upperStorage(
                    $auction->getwantCommodityId(),
                    $currentHighestBid->getMaxAmount()
                );

                $this->privateMessageSender->send(
                    14,
                    $currentHighestBid->getUserId(),
                    sprintf(
                        'Du wurdes bei einer Auktion des großen Nagus von %s überboten und hast %d %s zurück bekommen. Das aktuelle Gebot liegt bei: %d %s',
                        $user->getUserName(),
                        $currentHighestBid->getMaxAmount(),
                        $auction->getWantedCommodity()->getName(),
                        $newCurrentAmount,
                        $auction->getWantedCommodity()->getName()
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
                );
            }


            // change deals

            $auction->setAuctionAmount($newCurrentAmount);
            $auction->setAuctionUser($userId);
            $this->dealsRepository->save($auction);


            // create new bid
            $bid = $this->auctionBidRepository->prototype();
            $bid->setUser($game->getUser());
            $bid->setMaxAmount($maxAmount);
            $bid->setAuction($auction);
            $this->auctionBidRepository->save($bid);

            $game->addInformation(sprintf(_('Gebot wurde auf %d erhöht. Dein Maximalgebot liegt bei %d Du bist nun Meistbietender!'), $newCurrentAmount, $maxAmount));
        }

        if ($auction->getwantPrestige() !== null) {
            $description = sprintf(
                '-%d Prestige: Eingebüßt bei einer Auktion des Großen Nagus',
                $maxAmount
            );

            $this->createPrestigeLog->createLog(-$maxAmount, $description, $game->getUser(), time());

            if ($currentHighestBid->getUserId() > 100) {
                $descriptionsecond = sprintf(
                    '%d Prestige: Du wurdest bei einer Auktion des Großen Nagus überboten und hast dein Prestige zurück erhalten',
                    $currentHighestBid->getMaxAmount()
                );
                $this->createPrestigeLog->createLog($currentHighestBid->getMaxAmount(), $descriptionsecond, $currentHighestBid->getUser(), time());

                $this->privateMessageSender->send(
                    14,
                    $currentHighestBid->getUserId(),
                    sprintf(
                        'Du wurdest bei einer Auktion des großen Nagus von %s überboten und hast %d Prestige zurück bekommen. Das aktuelle Gebot liegt bei: %d Prestige',
                        $user->getUserName(),
                        $currentHighestBid->getMaxAmount(),
                        $newCurrentAmount
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
                );
            }
            // change deals

            $auction->setAuctionAmount($newCurrentAmount);
            $auction->setAuctionUser($userId);
            $this->dealsRepository->save($auction);

            // create new bid
            $bid = $this->auctionBidRepository->prototype();
            $bid->setUser($game->getUser());
            $bid->setMaxAmount($maxAmount);
            $bid->setAuction($auction);
            $this->auctionBidRepository->save($bid);

            $game->addInformation(sprintf(_('Gebot wurde auf %d erhöht. Dein Maximalgebot liegt bei %d Du bist nun Meistbietender!'), $newCurrentAmount, $maxAmount));
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsBidAuction;

use Stu\Component\Trade\TradeEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Entity\AuctionBidInterface;
use Stu\Orm\Entity\DealsInterface;
use Stu\Orm\Repository\AuctionBidRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class DealsBidAuction implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEALS_BID_AUCTION';

    private const BID_TYPE_FIRST = 0;
    private const BID_TYPE_RAISE_OWN = 1;
    private const BID_TYPE_RAISE_OTHER = 2;
    private const BID_TYPE_REVISE = 3;
    private const BID_TYPE_REVISE_OLD = 4;

    private DealsBidAuctionRequestInterface $dealsbidauctionRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private DealsRepositoryInterface $dealsRepository;

    private AuctionBidRepositoryInterface $auctionBidRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TradePostRepositoryInterface $tradepostRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private StorageRepositoryInterface $storageRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    private StuTime $stuTime;

    public function __construct(
        DealsBidAuctionRequestInterface $dealsbidauctionRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        AuctionBidRepositoryInterface $auctionBidRepository,
        DealsRepositoryInterface $dealsRepository,
        TradePostRepositoryInterface $tradepostRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        StorageRepositoryInterface $storageRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        CreatePrestigeLogInterface $createPrestigeLog,
        StuTime $stuTime
    ) {
        $this->dealsbidauctionRequest = $dealsbidauctionRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradepostRepository = $tradepostRepository;
        $this->dealsRepository = $dealsRepository;
        $this->auctionBidRepository = $auctionBidRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->storageRepository = $storageRepository;
        $this->createPrestigeLog = $createPrestigeLog;
        $this->stuTime = $stuTime;
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
        $time = $this->stuTime->time();

        // sanity checks
        if ($auction->getEnd() < $time || $auction->getStart() > $time) {
            return;
        }


        if ($maxAmount < 1 || $maxAmount <= $currentBidAmount) {
            $game->addInformation(_('Zu geringe Anzahl ausgewählt'));
            return;
        }

        if ($auction === null) {
            $game->addInformation(_('Die Auktion ist nicht mehr verfügbar'));
            return;
        }

        if (!$this->tradeLicenseRepository->hasFergLicense($userId)) {
            throw new AccessViolation(sprintf(
                _('UserId %d does not have license for Deals'),
                $userId
            ));
        }

        $highestBid = $auction->getHighestBid();
        if ($highestBid === null) {
            $this->createFirstBid($maxAmount, $auction, $game);
            return;
        }

        $userHasHighestBid = $highestBid->getUser() === $user;
        if ($userHasHighestBid) {
            $this->raiseOwnBid($maxAmount, $highestBid, $game, $auction);
            return;
        }

        $currentMaxAmount = $highestBid->getMaxAmount();

        if ($maxAmount <= $currentMaxAmount) {
            $this->raiseCurrentAmount($maxAmount, $auction, $game);
        }

        if ($maxAmount > $currentMaxAmount) {
            $this->setNewHighestBid($maxAmount, $auction, $game);
        }
    }

    private function createFirstBid(int $maxAmount, DealsInterface $auction, GameControllerInterface $game): void
    {
        //check if enough available
        if (!$this->checkAndCollectCosts($auction, $maxAmount, self::BID_TYPE_FIRST, $game)) {
            return;
        }

        $bid = $this->auctionBidRepository->prototype();
        $bid->setUser($game->getUser());
        $bid->setMaxAmount($maxAmount);
        $bid->setAuction($auction);
        $this->auctionBidRepository->save($bid);

        $auction->getAuctionBids()->add($bid);
        $auction->setAuctionUser($game->getUser()->getId());
        $auction->setAuctionAmount(1);
        $this->dealsRepository->save($auction);

        $game->addInformation(sprintf(_('Du hast das erste Gebot abgegeben. Dein Maximalgebot liegt bei %d'), $maxAmount));
    }

    private function raiseOwnBid(int $maxAmount, AuctionBidInterface $bid, GameControllerInterface $game, DealsInterface $auction): void
    {
        $currentHighestBid = $auction->getHighestBid();
        $additionalAmount = $maxAmount - $currentHighestBid->getMaxAmount();

        //check if enough available
        if (!$this->checkAndCollectCosts($auction, $additionalAmount, self::BID_TYPE_RAISE_OWN, $game)) {
            return;
        }

        $game->addInformation(sprintf(_('Dein Maximalgebot wurde auf %d erhöht'), $maxAmount));
        $bid->setMaxAmount($maxAmount);
        $this->auctionBidRepository->save($bid);
    }

    private function raiseCurrentAmount(int $maxAmount, DealsInterface $auction, GameControllerInterface $game): void
    {
        //check if enough available
        if (!$this->checkAndCollectCosts($auction, $maxAmount, self::BID_TYPE_RAISE_OTHER, $game)) {
            return;
        }

        $newAmount = min(
            $maxAmount + 1,
            $auction->getHighestBid()->getMaxAmount()
        );
        $auction->setAuctionAmount($newAmount);
        $this->dealsRepository->save($auction);

        $game->addInformation(sprintf(_('Dein Maximalgebot hat nicht ausgereicht. Höchstgebot liegt nun bei %d'), $newAmount));

        if ($auction->isPrestigeCost()) {
            $this->privateMessageSender->send(
                UserEnum::USER_NPC_FERG,
                $auction->getHighestBid()->getUserId(),
                sprintf(
                    'Ein Spieler hat auf ein Angebot bei "Deals des Großen Nagus" geboten, aber dein Maximalgebot nicht überschritten. Dein Höchstgebot liegt nun bei %d Prestige',
                    $newAmount,
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
            );
        } else {
            $this->privateMessageSender->send(
                UserEnum::USER_NPC_FERG,
                $auction->getHighestBid()->getUserId(),
                sprintf(
                    'Ein Spieler hat auf ein Angebot bei "Deals des Großen Nagus" geboten, aber dein Maximalgebot nicht überschritten. Dein Höchstgebot liegt nun bei %d %s',
                    $newAmount,
                    $auction->getWantedCommodity()->getName()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
            );
        }
    }


    private function setNewHighestBid(int $maxAmount, DealsInterface $auction, GameControllerInterface $game): void
    {
        //check if enough available
        if (!$this->checkAndCollectCosts($auction, $maxAmount, self::BID_TYPE_REVISE, $game)) {
            return;
        }

        // create new bid
        $bid = $this->auctionBidRepository->prototype();
        $bid->setUser($game->getUser());
        $bid->setMaxAmount($maxAmount);
        $bid->setAuction($auction);
        $this->auctionBidRepository->save($bid);

        // modify auction
        $auction->setAuctionAmount($auction->getHighestBid()->getMaxAmount() + 1);
        $auction->setAuctionUser($game->getUser()->getId());
        $auction->getAuctionBids()->add($bid);
        $this->dealsRepository->save($auction);

        $game->addInformation(sprintf(_('Gebot wurde auf %d erhöht. Dein Maximalgebot liegt bei %d. Du bist nun Meistbietender!'), $auction->getAuctionAmount(), $maxAmount));
    }

    private function checkAndCollectCosts(DealsInterface $auction, int $neededAmount, int $bidType, GameControllerInterface $game): bool
    {
        //check for sufficient amount
        if (!$this->isEnoughAvailable($auction, $neededAmount, $game)) {
            return false;
        }

        // raising does not need to collect
        if ($bidType === self::BID_TYPE_RAISE_OTHER) {
            return true;
        }

        $user = $game->getUser();
        $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);

        //reduce amount
        if ($auction->isPrestigeCost()) {
            $this->createPrestigeLog->createLog(
                -$neededAmount,
                sprintf($this->getPrestigeTemplate($bidType), $neededAmount),
                $user,
                time()
            );
        } else {
            $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $user);

            $storageManager->lowerStorage(
                $auction->getwantCommodityId(),
                $neededAmount
            );
        }

        //give back to previous
        if ($bidType === self::BID_TYPE_REVISE) {
            $currentHighestBid = $auction->getHighestBid();

            if ($auction->isPrestigeCost()) {
                $this->createPrestigeLog->createLog(
                    $currentHighestBid->getMaxAmount(),
                    sprintf($this->getPrestigeTemplate(self::BID_TYPE_REVISE_OLD), $currentHighestBid->getMaxAmount()),
                    $currentHighestBid->getUser(),
                    time()
                );

                $this->privateMessageSender->send(
                    UserEnum::USER_NPC_FERG,
                    $currentHighestBid->getUser()->getId(),
                    sprintf(
                        'Du wurdest bei einer Auktion des großen Nagus von %s überboten und hast %d Prestige zurück bekommen. Das aktuelle Gebot liegt bei: %d Prestige',
                        $user->getName(),
                        $currentHighestBid->getMaxAmount(),
                        $currentHighestBid->getMaxAmount() + 1
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
                );
            } else {
                $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $currentHighestBid->getUser());

                $storageManager->upperStorage(
                    $auction->getwantCommodityId(),
                    $currentHighestBid->getMaxAmount()
                );

                $this->privateMessageSender->send(
                    UserEnum::USER_NPC_FERG,
                    $currentHighestBid->getUser()->getId(),
                    sprintf(
                        'Du wurdest bei einer Auktion des großen Nagus von %s überboten und hast %d %s zurück bekommen. Das aktuelle Gebot liegt bei: %d %s',
                        $user->getName(),
                        $currentHighestBid->getMaxAmount(),
                        $auction->getWantedCommodity()->getName(),
                        $currentHighestBid->getMaxAmount() + 1,
                        $auction->getWantedCommodity()->getName()
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
                );
            }
        }

        return true;
    }

    private function getPrestigeTemplate(int $bidType): string
    {
        switch ($bidType) {
            case self::BID_TYPE_FIRST:
                return _('-%d Prestige: Eingebüßt durch Setzen eines Erstgebots bei einer Auktion des Großen Nagus');
            case self::BID_TYPE_RAISE_OWN:
                return _('-%d Prestige: Eingebüßt durch Erhöhen deines Maximalgebots bei einer Auktion des Großen Nagus');
            case self::BID_TYPE_REVISE_OLD:
                return _('%d Prestige: Du wurdest bei einer Auktion des Großen Nagus überboten und hast dein Prestige zurück erhalten');
            case self::BID_TYPE_REVISE:
                return _('-%d Prestige: Eingebüßt bei einer Auktion des Großen Nagus');
            default:
                return '';
        }
    }

    private function isEnoughAvailable(DealsInterface $auction, int $neededAmount, GameControllerInterface $game): bool
    {
        $userId = $game->getUser()->getId();

        if ($auction->getwantCommodityId() !== null) {
            $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
                TradeEnum::DEALS_FERG_TRADEPOST_ID,
                $userId,
                $auction->getWantCommodityId()
            );

            if ($storage === null || $storage->getAmount() < $neededAmount) {
                $game->addInformation(sprintf(
                    _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                    $auction->getWantedCommodity()->getName()
                ));
                return false;
            }
        }

        if ($auction->isPrestigeCost()) {
            $userprestige = $game->getUser()->getPrestige();

            if ($neededAmount > $userprestige) {
                $game->addInformation(sprintf(
                    _('Du hast nicht genügend Prestige, benötigt: %d'),
                    ($neededAmount - $userprestige)
                ));
                return false;
            }
        }

        return true;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsBidAuction;

use Override;
use RuntimeException;
use Stu\Component\Trade\TradeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Repository\AuctionBidRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class DealsBidAuction implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEALS_BID_AUCTION';

    private const int BID_TYPE_FIRST = 0;
    private const int BID_TYPE_RAISE_OWN = 1;
    private const int BID_TYPE_RAISE_OTHER = 2;
    private const int BID_TYPE_REVISE = 3;
    private const int BID_TYPE_REVISE_OLD = 4;

    public function __construct(private DealsBidAuctionRequestInterface $dealsbidauctionRequest, private TradeLibFactoryInterface $tradeLibFactory, private AuctionBidRepositoryInterface $auctionBidRepository, private DealsRepositoryInterface $dealsRepository, private TradePostRepositoryInterface $tradepostRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository, private StorageRepositoryInterface $storageRepository, private PrivateMessageSenderInterface $privateMessageSender, private CreatePrestigeLogInterface $createPrestigeLog, private StuTime $stuTime) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $dealId = $this->dealsbidauctionRequest->getDealId();
        $maxAmount = $this->dealsbidauctionRequest->getMaxAmount();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $auction = $this->dealsRepository->find($dealId);
        if ($auction === null) {
            $game->addInformation(_('Das Angebot ist nicht mehr verfügbar'));
            return;
        }

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

        if (!$this->tradeLicenseRepository->hasFergLicense($userId)) {
            throw new AccessViolationException(sprintf(
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
            $this->raiseCurrentAmount($maxAmount, $highestBid, $auction, $game);
        }

        if ($maxAmount > $currentMaxAmount) {
            $this->setNewHighestBid($maxAmount, $highestBid, $auction, $game);
        }
    }

    private function createFirstBid(int $maxAmount, Deals $auction, GameControllerInterface $game): void
    {
        //check if enough available
        if (!$this->checkAndCollectCosts($auction, null, $maxAmount, self::BID_TYPE_FIRST, $game)) {
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

    private function raiseOwnBid(int $maxAmount, AuctionBid $bid, GameControllerInterface $game, Deals $auction): void
    {
        $additionalAmount = $maxAmount - $bid->getMaxAmount();

        //check if enough available
        if (!$this->checkAndCollectCosts($auction, $bid, $additionalAmount, self::BID_TYPE_RAISE_OWN, $game)) {
            return;
        }

        $game->addInformation(sprintf(_('Dein Maximalgebot wurde auf %d erhöht'), $maxAmount));
        $bid->setMaxAmount($maxAmount);
        $this->auctionBidRepository->save($bid);
    }

    private function raiseCurrentAmount(int $maxAmount, AuctionBid $highestBid, Deals $auction, GameControllerInterface $game): void
    {
        //check if enough available
        if (!$this->checkAndCollectCosts($auction, $highestBid, $maxAmount, self::BID_TYPE_RAISE_OTHER, $game)) {
            return;
        }

        $newAmount = min(
            $maxAmount + 1,
            $highestBid->getMaxAmount()
        );
        $auction->setAuctionAmount($newAmount);
        $this->dealsRepository->save($auction);

        $game->addInformation(sprintf(_('Dein Maximalgebot hat nicht ausgereicht. Höchstgebot liegt nun bei %d'), $newAmount));

        $wantedCommodity = $auction->getWantedCommodity();
        if ($wantedCommodity === null) {
            $this->privateMessageSender->send(
                UserEnum::USER_NPC_FERG,
                $highestBid->getUserId(),
                sprintf(
                    'Ein Spieler hat auf ein Angebot bei "Deals des Großen Nagus" geboten, aber dein Maximalgebot nicht überschritten. Dein Höchstgebot liegt nun bei %d Prestige',
                    $newAmount,
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE
            );
        } else {
            $this->privateMessageSender->send(
                UserEnum::USER_NPC_FERG,
                $highestBid->getUserId(),
                sprintf(
                    'Ein Spieler hat auf ein Angebot bei "Deals des Großen Nagus" geboten, aber dein Maximalgebot nicht überschritten. Dein Höchstgebot liegt nun bei %d %s',
                    $newAmount,
                    $wantedCommodity->getName()
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE
            );
        }
    }


    private function setNewHighestBid(int $maxAmount, AuctionBid $highestBid, Deals $auction, GameControllerInterface $game): void
    {
        //check if enough available
        if (!$this->checkAndCollectCosts($auction, $highestBid, $maxAmount, self::BID_TYPE_REVISE, $game)) {
            return;
        }

        // create new bid
        $bid = $this->auctionBidRepository->prototype();
        $bid->setUser($game->getUser());
        $bid->setMaxAmount($maxAmount);
        $bid->setAuction($auction);
        $this->auctionBidRepository->save($bid);

        // modify auction
        $auction->setAuctionAmount($highestBid->getMaxAmount() + 1);
        $auction->setAuctionUser($game->getUser()->getId());
        $auction->getAuctionBids()->add($bid);
        $this->dealsRepository->save($auction);

        $game->addInformation(sprintf(_('Gebot wurde auf %d erhöht. Dein Maximalgebot liegt bei %d. Du bist nun Meistbietender!'), $auction->getAuctionAmount(), $maxAmount));
    }

    private function checkAndCollectCosts(Deals $auction, ?AuctionBid $currentHighestBid, int $neededAmount, int $bidType, GameControllerInterface $game): bool
    {
        //check for sufficient amount
        if (!$this->isEnoughAvailable($auction, $neededAmount, $game)) {
            return false;
        }

        // raising does not need to collect
        if ($bidType === self::BID_TYPE_RAISE_OTHER || $currentHighestBid === null) {
            return true;
        }

        $user = $game->getUser();
        $tradePost = $this->tradepostRepository->find(TradeEnum::DEALS_FERG_TRADEPOST_ID);
        if ($tradePost === null) {
            throw new RuntimeException('no deals ferg tradepost found');
        }

        //reduce amount

        $wantedCommodity = $auction->getWantedCommodity();
        if ($wantedCommodity === null) {
            $this->createPrestigeLog->createLog(
                -$neededAmount,
                sprintf($this->getPrestigeTemplate($bidType), $neededAmount),
                $user,
                time()
            );
        } else {
            $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $user);

            $storageManager->lowerStorage(
                $wantedCommodity->getId(),
                $neededAmount
            );
        }

        //give back to previous
        if ($bidType === self::BID_TYPE_REVISE) {

            $wantedCommodity = $auction->getWantedCommodity();
            if ($wantedCommodity === null) {
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
                    PrivateMessageFolderTypeEnum::SPECIAL_TRADE
                );
            } else {
                $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $currentHighestBid->getUser());

                $storageManager->upperStorage(
                    $wantedCommodity->getId(),
                    $currentHighestBid->getMaxAmount()
                );

                $this->privateMessageSender->send(
                    UserEnum::USER_NPC_FERG,
                    $currentHighestBid->getUser()->getId(),
                    sprintf(
                        'Du wurdest bei einer Auktion des großen Nagus von %s überboten und hast %d %s zurück bekommen. Das aktuelle Gebot liegt bei: %d %s',
                        $user->getName(),
                        $currentHighestBid->getMaxAmount(),
                        $wantedCommodity->getName(),
                        $currentHighestBid->getMaxAmount() + 1,
                        $wantedCommodity->getName()
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_TRADE
                );
            }
        }

        return true;
    }

    private function getPrestigeTemplate(int $bidType): string
    {
        return match ($bidType) {
            self::BID_TYPE_FIRST => _('-%d Prestige: Eingebüßt durch Setzen eines Erstgebots bei einer Auktion des Großen Nagus'),
            self::BID_TYPE_RAISE_OWN => _('-%d Prestige: Eingebüßt durch Erhöhen deines Maximalgebots bei einer Auktion des Großen Nagus'),
            self::BID_TYPE_REVISE_OLD => _('%d Prestige: Du wurdest bei einer Auktion des Großen Nagus überboten und hast dein Prestige zurück erhalten'),
            self::BID_TYPE_REVISE => _('-%d Prestige: Eingebüßt bei einer Auktion des Großen Nagus'),
            default => '',
        };
    }

    private function isEnoughAvailable(Deals $auction, int $neededAmount, GameControllerInterface $game): bool
    {
        $userId = $game->getUser()->getId();

        $wantedCommodity = $auction->getWantedCommodity();
        if ($wantedCommodity !== null) {
            $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
                TradeEnum::DEALS_FERG_TRADEPOST_ID,
                $userId,
                $wantedCommodity->getId()
            );

            if ($storage === null || $storage->getAmount() < $neededAmount) {
                $game->addInformation(sprintf(
                    _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                    $wantedCommodity->getName()
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}

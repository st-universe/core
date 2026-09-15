<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\OrionBidAuction;

use RuntimeException;
use Stu\Component\Trade\TradeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\Lib\TradePostStorageManagerInterface;
use Stu\Module\Trade\View\ShowOrionSlaveTrade\ShowOrionSlaveTrade;
use Stu\Orm\Entity\OrionAuction;
use Stu\Orm\Entity\OrionAuctionBid;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\OrionAuctionBidRepositoryInterface;
use Stu\Orm\Repository\OrionAuctionRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class OrionBidAuction implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ORION_BID_AUCTION';

    public function __construct(
        private OrionBidAuctionRequestInterface $request,
        private OrionAuctionRepositoryInterface $orionAuctionRepository,
        private OrionAuctionBidRepositoryInterface $orionAuctionBidRepository,
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private StorageRepositoryInterface $storageRepository,
        private TradeLibFactoryInterface $tradeLibFactory,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StuTime $stuTime
    ) {}

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowOrionSlaveTrade::VIEW_IDENTIFIER);
        $user = $game->getUser();
        $auction = $this->orionAuctionRepository->find($this->request->getAuctionId());
        $amount = $this->request->getAmount();

        $time = $this->stuTime->time();
        if ($auction === null || $auction->getStart() > $time || $auction->getEnd() <= $time || $auction->getCompletedAt() !== null) {
            $game->getInfo()->addInformation('Die Auktion ist nicht mehr verfügbar');
            return;
        }
        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($user->getId(), TradeEnum::ORION_ZAGOS_TRADEPOST_ID)) {
            throw new AccessViolationException(sprintf('UserId %d does not have license for Zagos', $user->getId()));
        }
        if ($amount < 1 || $amount <= $auction->getAuctionAmount()) {
            $game->getInfo()->addInformation('Das Gebot muss höher als das aktuelle Gebot sein');
            return;
        }

        $highestBid = $this->orionAuctionBidRepository->getHighestBid($auction);
        if ($highestBid === null) {
            if (!$this->collect($auction, $amount, $game)) {
                return;
            }
            $bid = $this->orionAuctionBidRepository->prototype()
                ->setAuction($auction)
                ->setUser($user)
                ->setMaxAmount($amount);
            $this->orionAuctionBidRepository->save($bid);
            $auction->setAuctionAmount($amount);
            $this->orionAuctionRepository->save($auction);
            $game->getInfo()->addInformationf('Du bist mit %d aktuell Meistbietender', $amount);
            return;
        }

        if ($highestBid->getUser()->getId() === $user->getId()) {
            $additionalAmount = $amount - $highestBid->getMaxAmount();
            if ($additionalAmount < 1) {
                $game->getInfo()->addInformation('Dein neues Gebot muss höher sein');
                return;
            }
            if (!$this->collect($auction, $additionalAmount, $game)) {
                return;
            }
            $highestBid->setMaxAmount($amount);
            $this->orionAuctionBidRepository->save($highestBid);
            $auction->setAuctionAmount($amount);
            $this->orionAuctionRepository->save($auction);
            $game->getInfo()->addInformationf('Dein Gebot wurde auf %d erhöht', $amount);
            return;
        }

        if (!$this->collect($auction, $amount, $game)) {
            return;
        }
        $this->refund($auction, $highestBid);
        $this->sendOverbidMessage($auction, $highestBid);
        $this->orionAuctionBidRepository->delete($highestBid);
        $this->orionAuctionBidRepository->save(
            $this->orionAuctionBidRepository->prototype()
                ->setAuction($auction)
                ->setUser($user)
                ->setMaxAmount($amount)
        );
        $auction->setAuctionAmount($amount);
        $this->orionAuctionRepository->save($auction);
        $game->getInfo()->addInformationf('Du bist mit %d aktuell Meistbietender', $amount);
    }

    private function collect(OrionAuction $auction, int $amount, GameControllerInterface $game): bool
    {
        if (!$this->hasEnough($auction, $amount, $game)) {
            return false;
        }

        $wantedCommodity = $auction->getWantedCommodity();
        $user = $game->getUser();
        if ($wantedCommodity === null) {
            $this->createPrestigeLog->createLog(-$amount, sprintf('-%d Prestige: Orion-Auktion', $amount), $user, $this->stuTime->time());
            return true;
        }

        $this->getZagosStorageManager($user)->lowerStorage($wantedCommodity->getId(), $amount);
        return true;
    }

    private function hasEnough(OrionAuction $auction, int $amount, GameControllerInterface $game): bool
    {
        $wantedCommodity = $auction->getWantedCommodity();
        $user = $game->getUser();
        if ($wantedCommodity === null) {
            if ($user->getPrestige() < $amount) {
                $game->getInfo()->addInformation('Du hast nicht genügend Prestige');
                return false;
            }
            return true;
        }

        $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
            TradeEnum::ORION_ZAGOS_TRADEPOST_ID,
            $user->getId(),
            $wantedCommodity->getId()
        );
        if ($storage === null || $storage->getAmount() < $amount) {
            $game->getInfo()->addInformationf('Es befindet sich nicht genügend %s auf deinem Warenkonto bei Zagos', $wantedCommodity->getName());
            return false;
        }
        return true;
    }

    private function refund(OrionAuction $auction, OrionAuctionBid $bid): void
    {
        $wantedCommodity = $auction->getWantedCommodity();
        if ($wantedCommodity === null) {
            $this->createPrestigeLog->createLog($bid->getMaxAmount(), sprintf('%d Prestige: Rückerstattung aus der Orion-Auktion', $bid->getMaxAmount()), $bid->getUser(), $this->stuTime->time());
            return;
        }
        $this->getZagosStorageManager($bid->getUser())->upperStorage($wantedCommodity->getId(), $bid->getMaxAmount());
    }

    private function sendOverbidMessage(OrionAuction $auction, OrionAuctionBid $bid): void
    {
        $wantedCommodity = $auction->getWantedCommodity();
        $currency = $wantedCommodity?->getName() ?? 'Prestige';
        $this->privateMessageSender->send(
            UserConstants::USER_NPC_FERG,
            $bid->getUser()->getId(),
            sprintf(
                'Du wurdest bei einer Orion-Auktion überboten. Dein Gebot von %d %s wurde dir zurückerstattet',
                $bid->getMaxAmount(),
                $currency
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE
        );
    }

    private function getZagosStorageManager(User $user): TradePostStorageManagerInterface
    {
        $tradePost = $this->tradePostRepository->find(TradeEnum::ORION_ZAGOS_TRADEPOST_ID);
        if ($tradePost === null) {
            throw new RuntimeException('no Zagos tradepost found');
        }
        return $this->tradeLibFactory->createTradePostStorageManager($tradePost, $user);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

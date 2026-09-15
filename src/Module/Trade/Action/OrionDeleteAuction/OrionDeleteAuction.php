<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\OrionDeleteAuction;

use RuntimeException;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowOrionSlaveTrade\ShowOrionSlaveTrade;
use Stu\Orm\Entity\OrionAuction;
use Stu\Orm\Entity\OrionAuctionBid;
use Stu\Orm\{Repository\CrewRepositoryInterface, Repository\OrionAuctionBidRepositoryInterface, Repository\OrionAuctionRepositoryInterface, Repository\TradePostRepositoryInterface};

final class OrionDeleteAuction implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_ORION_AUCTION';

    public function __construct(
        private OrionDeleteAuctionRequestInterface $request,
        private OrionAuctionRepositoryInterface $orionAuctionRepository,
        private OrionAuctionBidRepositoryInterface $orionAuctionBidRepository,
        private CrewRepositoryInterface $crewRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private TradeLibFactoryInterface $tradeLibFactory,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private StuTime $stuTime
    ) {}

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowOrionSlaveTrade::VIEW_IDENTIFIER);
        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation('Aktion nicht möglich');
            return;
        }

        $auction = $this->orionAuctionRepository->find($this->request->getAuctionId());
        if ($auction === null || $auction->getCompletedAt() !== null) {
            $game->getInfo()->addInformation('Die Auktion ist nicht verfügbar');
            return;
        }

        $highestBid = $this->orionAuctionBidRepository->getHighestBid($auction);
        if ($highestBid !== null) {
            $this->refund($auction, $highestBid);
        }
        foreach ($this->orionAuctionBidRepository->getByAuction($auction) as $bid) {
            $this->orionAuctionBidRepository->delete($bid);
        }

        $this->orionAuctionRepository->delete($auction);
        $this->crewRepository->delete($auction->getCrew());
        $game->getInfo()->addInformation('Die Orion-Auktion wurde gelöscht');
    }

    private function refund(OrionAuction $auction, OrionAuctionBid $bid): void
    {
        $wantedCommodity = $auction->getWantedCommodity();
        if ($wantedCommodity === null) {
            $amount = $bid->getMaxAmount();
            $this->createPrestigeLog->createLog(
                $amount,
                sprintf('%d Prestige: Rückerstattung aus abgebrochener Orion-Auktion', $amount),
                $bid->getUser(),
                $this->stuTime->time()
            );
            return;
        }

        $tradePost = $this->tradePostRepository->find(TradeEnum::ORION_ZAGOS_TRADEPOST_ID);
        if ($tradePost === null) {
            throw new RuntimeException('no Zagos tradepost found');
        }
        $this->tradeLibFactory->createTradePostStorageManager($tradePost, $bid->getUser())
            ->upperStorage($wantedCommodity->getId(), $bid->getMaxAmount());
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

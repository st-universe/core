<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateOffer;

use Stu\Exception\AccessViolation;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;

final class CreateOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_OFFER';

    private CreateOfferRequestInterface $createOfferRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    public function __construct(
        CreateOfferRequestInterface $createOfferRequest,
        CommodityRepositoryInterface $commodityRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository
    ) {
        $this->createOfferRequest = $createOfferRequest;
        $this->commodityRepository = $commodityRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowAccounts::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $storage = $this->tradeStorageRepository->find($this->createOfferRequest->getStorageId());
        if ($storage === null) {
            throw new AccessViolation(sprintf("Storage not existent! Fool: %d", $userId));
        }
        if ($storage->getUserId() !== $userId) {
            throw new AccessViolation(sprintf("Storage belongs to other user! Fool: %d", $userId));
        }

        $trade_post = $storage->getTradePost();

        $giveGoodId = $this->createOfferRequest->getGiveGoodId();
        $giveAmount = $this->createOfferRequest->getGiveAmount();
        $wantedGoodId = $this->createOfferRequest->getWantedGoodId();
        $wantedAmount = $this->createOfferRequest->getWantedAmount();
        $offerAmount = $this->createOfferRequest->getOfferAmount();

        if ($giveGoodId === $wantedGoodId) {
            $game->addInformation("Es kann nicht die gleiche Ware eingetauscht werden");
            return;
        }
        if ($giveAmount < 1) {
            $game->addInformation("Es wurde keine Angebotene Menge angeben");
            return;
        }

        if ($wantedAmount < 1) {
            $game->addInformation("Es wurde keine Verlangte Menge");
            return;
        }

        if ($offerAmount < 1) {
            $game->addInformation("Es wurde keine Anzahl an Angeboten angegeben");
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($trade_post, $userId);

        if ($storageManager->getFreeStorage() <= 0) {
            $game->addInformation("Dein Warenkonto auf diesem Handelsposten ist überfüllt - Angebot kann nicht erstellt werden");
            return;
        }

        if ($giveGoodId == CommodityTypeEnum::GOOD_LATINUM) {
            $result = array_filter(
                $this->commodityRepository->getViewable(),
                function (CommodityInterface $commodity) use ($wantedGoodId): bool {
                    return $commodity->getId() === $wantedGoodId;
                }
            );
            if ($result === []) {
                return;
            }
        }

        if ($offerAmount < 1 || $offerAmount > 99) {
            $offerAmount = 1;
        }
        if ($offerAmount * $giveAmount > $storage->getAmount()) {
            $offerAmount = floor($storage->getAmount() / $giveAmount);
        }
        if ($offerAmount < 1) {
            return;
        }
        $offer = $this->tradeOfferRepository->prototype();
        $offer->setUser($game->getUser());
        $offer->setTradePost($trade_post);
        $offer->setDate(time());
        $offer->setOfferedCommodity($this->commodityRepository->find($giveGoodId));
        $offer->setOfferedGoodCount((int) $giveAmount);
        $offer->setWantedCommodity($this->commodityRepository->find($wantedGoodId));
        $offer->setWantedGoodCount((int) $wantedAmount);
        $offer->setOfferCount((int) $offerAmount);

        $this->tradeOfferRepository->save($offer);

        $storageManager->lowerStorage($giveGoodId, (int) $offerAmount * $giveAmount);

        $game->addInformation('Das Angebot wurde erstellt');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
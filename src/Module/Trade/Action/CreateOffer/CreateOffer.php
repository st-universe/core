<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateOffer;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class CreateOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_OFFER';

    private CreateOfferRequestInterface $createOfferRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        CreateOfferRequestInterface $createOfferRequest,
        CommodityRepositoryInterface $commodityRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->createOfferRequest = $createOfferRequest;
        $this->commodityRepository = $commodityRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->storageRepository = $storageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowAccounts::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $storage = $this->storageRepository->find($this->createOfferRequest->getStorageId());
        if ($storage === null) {
            $game->addInformation(_('Waren zum Erstellen des Angebots nicht gefunden'));
            return;
        }
        if ($storage->getUserId() !== $userId) {
            throw new AccessViolation(sprintf("Storage belongs to other user! Fool: %d", $userId));
        }

        $tradePost = $storage->getTradePost();

        if ($tradePost->getUserId() === UserEnum::USER_NOONE) {
            $game->addInformation(_('Dieser Handelsposten wurde verlassen. Handel ist nicht mehr möglich.'));
            return;
        }

        $giveCommodityId = $this->createOfferRequest->getGiveCommodityId();
        $giveAmount = $this->createOfferRequest->getGiveAmount();
        $wantedCommodityId = $this->createOfferRequest->getWantedCommodityId();
        $wantedAmount = $this->createOfferRequest->getWantedAmount();
        $offerAmount = $this->createOfferRequest->getOfferAmount();

        if ($giveCommodityId === $wantedCommodityId) {
            $game->addInformation("Es kann nicht die gleiche Ware eingetauscht werden");
            return;
        }
        if ($giveAmount < 1) {
            $game->addInformation("Es wurde keine angebotene Menge angeben");
            return;
        }

        if ($wantedAmount < 1) {
            $game->addInformation("Es wurde keine verlangte Menge");
            return;
        }

        if ($offerAmount < 1) {
            $game->addInformation("Es wurde keine Anzahl an Angeboten angegeben");
            return;
        }

        $offeredCommodity = $this->commodityRepository->find($giveCommodityId);
        if ($offeredCommodity === null) {
            return;
        }
        $wantedCommodity = $this->commodityRepository->find($wantedCommodityId);
        if ($wantedCommodity === null) {
            return;
        }

        if ($offeredCommodity->isBoundToAccount()) {
            $game->addInformation("Diese Ware kann nicht gehandelt werden");
            return;
        }

        // is tradeable?
        if (!$offeredCommodity->isTradeable() || !$wantedCommodity->isTradeable()) {
            return;
        }

        // is there already an equal offer?
        if ($this->isEquivalentOfferExistent(
            $userId,
            $tradePost->getId(),
            $giveCommodityId,
            $giveAmount,
            $wantedCommodityId,
            $wantedAmount
        )) {
            $game->addInformation("Du hast auf diesem Handelsposten bereits ein vergleichbares Angebot");
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $game->getUser());

        if ($storageManager->getFreeStorage() <= 0) {
            $game->addInformation("Dein Warenkonto auf diesem Handelsposten ist überfüllt - Angebot kann nicht erstellt werden");
            return;
        }

        if ($offerAmount > 99) {
            $offerAmount = 1;
        }
        if ($offerAmount * $giveAmount > $storage->getAmount()) {
            $offerAmount = (int)(floor($storage->getAmount() / $giveAmount));
        }
        if ($offerAmount < 1) {
            return;
        }

        $offer = $this->saveOffer(
            $game->getUser(),
            $tradePost,
            $offeredCommodity,
            $giveAmount,
            $wantedCommodity,
            $wantedAmount,
            $offerAmount
        );

        $this->saveStorage($offer);

        $storageManager->lowerStorage($giveCommodityId, $offerAmount * $giveAmount);


        $game->addInformation('Das Angebot wurde erstellt');
    }

    private function saveOffer(
        UserInterface $user,
        TradePostInterface $tradePost,
        CommodityInterface $offeredCommodity,
        int $giveAmount,
        CommodityInterface $wantedCommodity,
        int $wantedAmount,
        int $offerAmount
    ): TradeOfferInterface {
        $offer = $this->tradeOfferRepository->prototype();
        $offer->setUser($user);
        $offer->setTradePost($tradePost);
        $offer->setDate(time());
        $offer->setOfferedCommodity($offeredCommodity);
        $offer->setOfferedCommodityCount($giveAmount);
        $offer->setWantedCommodity($wantedCommodity);
        $offer->setWantedCommodityCount($wantedAmount);
        $offer->setOfferCount($offerAmount);

        $this->tradeOfferRepository->save($offer);

        return $offer;
    }

    private function saveStorage(TradeOfferInterface $tradeOffer): void
    {
        $storage = $this->storageRepository->prototype();
        $storage->setUser($tradeOffer->getUser());
        $storage->setTradeOffer($tradeOffer);
        $storage->setCommodity($tradeOffer->getOfferedCommodity());
        $storage->setAmount($tradeOffer->getOfferedCommodityCount() * $tradeOffer->getOfferCount());

        $this->storageRepository->save($storage);
    }

    private function isEquivalentOfferExistent(
        int $userId,
        int $tradePostId,
        int $giveCommodityId,
        int $giveAmount,
        int $wantedCommodityId,
        int $wantedAmount
    ): bool {
        $offers = $this->tradeOfferRepository->getByTradePostAndUserAndCommodities($tradePostId, $userId, $giveCommodityId, $wantedCommodityId);

        foreach ($offers as $offer) {
            if (round($giveAmount / $wantedAmount, 2) === round($offer->getOfferedCommodityCount() / $offer->getWantedCommodityCount(), 2)) {
                return true;
            }
        }

        return false;
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}

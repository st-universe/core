<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateOffer;

use AccessViolation;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use TradeOfferData;
use TradePost;
use TradeStorage;

final class CreateOffer implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CREATE_OFFER';

    private $createOfferRequest;

    private $commodityRepository;

    private $tradeLibFactory;

    public function __construct(
        CreateOfferRequestInterface $createOfferRequest,
        CommodityRepositoryInterface $commodityRepository,
        TradeLibFactoryInterface $tradeLibFactory
    ) {
        $this->createOfferRequest = $createOfferRequest;
        $this->commodityRepository = $commodityRepository;
        $this->tradeLibFactory = $tradeLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $storage = new TradeStorage($this->createOfferRequest->getStorageId());
        if ((int) $storage->getUserId() !== $userId) {
            throw new AccessViolation();
        }
        $trade_post = new TradePost($storage->getTradePostId());

        $giveGoodId = $this->createOfferRequest->getGiveGoodId();
        $giveAmount = $this->createOfferRequest->getGiveAmount();
        $wantedGoodId = $this->createOfferRequest->getWantedGoodId();
        $wantedAmount = $this->createOfferRequest->getWantedAmount();
        $offerAmount = $this->createOfferRequest->getOfferAmount();

        if ($giveGoodId === $wantedGoodId) {
            return;
        }
        if ($giveAmount < 1 || $wantedAmount < 1) {
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($trade_post, $userId);

        if ($storageManager->getFreeStorage() <= 0) {
            $game->setView(ShowAccounts::VIEW_IDENTIFIER);
            $game->addInformation("Dein Warenkonto auf diesem Handelsposten ist überfüllt - Angebot kann nicht erstellt werden");
            return;
        }

        if ($giveGoodId == CommodityTypeEnum::GOOD_DILITHIUM) {
            $result = array_filter(
                $this->commodityRepository->getViewable(),
                function (CommodityInterface $commodity) use ($wantedGoodId): bool {
                    return $commodity->getId() === $wantedGoodId;
                }
            );
            if ($result === []) {
                return;
            }
        } else {
            if ($wantedGoodId != CommodityTypeEnum::GOOD_DILITHIUM) {
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
        $offer = new TradeOfferData();
        $offer->setUserId($userId);
        $offer->setTradePostId($storage->getTradePostId());
        $offer->setDate(time());
        $offer->setOfferedGoodId($giveGoodId);
        $offer->setOfferedGoodCount($giveAmount);
        $offer->setWantedGoodId($wantedGoodId);
        $offer->setWantedGoodCount($wantedAmount);
        $offer->setOfferCount($offerAmount);
        $offer->save();

        if ($storage->getAmount() <= $offerAmount * $giveAmount) {
            $storage->deleteFromDatabase();
        } else {
            $storage->lowerCount($offerAmount * $giveAmount);
            $storage->save();
        }

        $game->addInformation('Das Angebot wurde erstellt');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeOffer;

use Stu\Exception\AccessViolation;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\Overview\Overview;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class DealsTakeOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEALS_TAKE_OFFER';

    private DealsTakeOfferRequestInterface $dealstakeOfferRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private DealsRepositoryInterface $dealsRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TradeTransactionRepositoryInterface $tradeTransactionRepository;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        DealsTakeOfferRequestInterface $dealstakeOfferRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        DealsRepositoryInterface $dealsRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        TradeTransactionRepositoryInterface $tradeTransactionRepository,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->dealstakeOfferRequest = $dealstakeOfferRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->dealsRepository = $dealsRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->tradeTransactionRepository = $tradeTransactionRepository;
        $this->storageRepository = $storageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $dealId = $this->dealstakeOfferRequest->getDealId();
        $amount = $this->dealstakeOfferRequest->getAmount();

        if ($amount < 1) {
            return;
        }

        $selectedDeal = $this->dealsRepository->find($dealId);

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

        $tradePost = $storage->getTradePost();

        $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $userId);

        $freeStorage = $storageManagerUser->getFreeStorage();

        if (
            $freeStorage <= 0 &&
            $selectedDeal->getgiveCommodityAmount() > $selectedDeal->getwantCommodityAmount()
        ) {
            $game->addInformation(_('Dein Warenkonto auf diesem Handelsposten ist voll'));
            return;
        }
        if ($amount * $selectedDeal->getwantCommodityAmount() > $storage->getAmount()) {
            $amount = (int) floor($storage->getAmount() / $selectedDeal->getwantCommodityAmount());
        }
        if ($amount * $selectedDeal->getgiveCommodityAmount() - $amount * $selectedDeal->getwantCommodityAmount() > $freeStorage) {
            $amount = (int) floor($freeStorage / ($selectedDeal->getgiveCommodityAmount() - $selectedDeal->getwantCommodityAmount()));
            if ($amount <= 0) {
                $game->addInformation(_('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung'));
                return;
            }
        }

        if ($selectedDeal->getAmount() <= $amount) {
            $amount = $selectedDeal->getAmount();

            $this->storageRepository->delete($selectedDeal->getStorage());
            $this->dealsRepository->delete($selectedDeal);
        } else {

            //modify deal
            $selectedDeal->setAmount($selectedDeal->getAmount() - (int) $amount);
            $this->dealsRepository->save($selectedDeal);
        }

        $storageManagerUser->upperStorage(
            (int) $selectedDeal->getgiveCommodityId(),
            (int) $selectedDeal->getgiveCommodityAmount() * $amount
        );

        $storageManagerUser->lowerStorage(
            (int) $selectedDeal->getwantCommodityId(),
            (int) $selectedDeal->getwantCommodityAmount() * $amount
        );

        $game->addInformation(sprintf(_('Das Angebot wurde %d mal angenommen'), $amount));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
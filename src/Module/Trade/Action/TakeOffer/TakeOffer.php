<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TakeOffer;

use AccessViolation;
use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;

final class TakeOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TAKE_OFFER';

    private $takeOfferRequest;

    private $tradeLibFactory;

    private $tradePostRepository;

    private $tradeOfferRepository;

    private $tradeLicenseRepository;

    private $tradeStorageRepository;

    public function __construct(
        TakeOfferRequestInterface $takeOfferRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository
    ) {
        $this->takeOfferRequest = $takeOfferRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $offerId = $this->takeOfferRequest->getOfferId();
        $amount = $this->takeOfferRequest->getAmount();

        /** @var TradeOfferInterface $selectedOffer */
        $selectedOffer = $this->tradeOfferRepository->find($offerId);

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $selectedOffer->getTradePost()->getId())) {
            throw new AccessViolation();
        }

        if ($userId === $selectedOffer->getUserId()) {
            return;
        }

        $storage = $this->tradeStorageRepository->getByTradepostAndUserAndCommodity(
            $selectedOffer->getTradePostId(),
            $userId,
            $selectedOffer->getWantedGoodId()
        );

        if ($storage === null || $storage->getAmount() < $selectedOffer->getWantedGoodCount()) {
            $game->addInformation(sprintf(
                _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                $selectedOffer->getWantedCommodity()->getName()
            ));
            return;
        }

        /** @var TradePostInterface $tradePost */
        $tradePost = $this->tradePostRepository->find((int) $storage->getTradePostId());
        if ($tradePost === null) {
            return;
        }

        $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $userId);
        $storageManagerRemote = $this->tradeLibFactory->createTradePostStorageManager($tradePost, (int) $selectedOffer->getUserId());

        $freeStorage = $storageManagerUser->getFreeStorage();

        if (
            $freeStorage <= 0 &&
            $selectedOffer->getOfferedGoodCount() > $selectedOffer->getWantedGoodCount()
        ) {
            $game->addInformation(_('Dein Warenkonto auf diesem Handelsposten ist voll'));
            return;
        }
        if ($amount * $selectedOffer->getWantedGoodCount() > $storage->getAmount()) {
            $amount = floor($storage->getAmount() / $selectedOffer->getWantedGoodCount());
        }
        if ($amount * $selectedOffer->getOfferedGoodCount() - $amount * $selectedOffer->getWantedGoodCount() > $freeStorage) {
            $amount = floor($freeStorage / ($selectedOffer->getOfferedGoodCount() - $selectedOffer->getWantedGoodCount()));
            if ($amount <= 0) {
                $game->addInformation(_('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung'));
                return;
            }
        }

        if ($selectedOffer->getOfferCount() <= $amount) {
            $amount = $selectedOffer->getOfferCount();

            $this->tradeOfferRepository->delete($selectedOffer);
        } else {
            $selectedOffer->setOfferCount($selectedOffer->getOfferCount() - $amount);

            $this->tradeOfferRepository->save($selectedOffer);
        }

        $storageManagerRemote->upperStorage(
            (int) $selectedOffer->getWantedGoodId(),
            (int) $selectedOffer->getWantedGoodCount() * $amount
        );

        $storageManagerUser->upperStorage(
            (int) $selectedOffer->getOfferedGoodId(),
            (int) $selectedOffer->getOfferedGoodCount() * $amount
        );

        $storageManagerUser->lowerStorage(
            (int) $selectedOffer->getWantedGoodId(),
            (int) $selectedOffer->getWantedGoodCount() * $amount
        );

        $game->addInformation(sprintf(_('Das Angebot wurde %d mal angenommen'), $amount));

        PM::sendPM(
            $userId,
            $selectedOffer->getUserId(),
            sprintf(
                'Es wurden insgesamt %d %s gegen %d %s getauscht',
                $selectedOffer->getOfferedGoodCount() * $amount,
                $selectedOffer->getOfferedCommodity()->getName(),
                $selectedOffer->getWantedGoodCount() * $amount,
                $selectedOffer->getWantedCommodity()->getName()
            ),
            PM_SPECIAL_TRADE
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

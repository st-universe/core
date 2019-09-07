<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TakeOffer;

use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use TradeOffer;
use TradeStorage;

final class TakeOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TAKE_OFFER';

    private $takeOfferRequest;

    private $tradeLibFactory;

    private $tradePostRepository;

    public function __construct(
        TakeOfferRequestInterface $takeOfferRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->takeOfferRequest = $takeOfferRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $offerId = $this->takeOfferRequest->getOfferId();
        $amount = $this->takeOfferRequest->getAmount();

        $selectedOffer = new TradeOffer($offerId);

        // @todo check if user may acces the offer (tradepost)

        if ($userId === $selectedOffer->getUserId()) {
            return;
        }

        $storage = TradeStorage::getStorageByGood(
            $selectedOffer->getTradePostId(),
            $userId,
            $selectedOffer->getWantedGoodId()
        );

        if (!$storage || $storage->getAmount() < $selectedOffer->getWantedGoodCount()) {
            $game->addInformation(sprintf(
                _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                $selectedOffer->getWantedGoodObject()->getName()
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
            $selectedOffer->deleteFromDatabase();
        } else {
            $selectedOffer->lowerOfferCount($amount);
            $selectedOffer->save();
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
                $selectedOffer->getOfferedGoodObject()->getName(),
                $selectedOffer->getWantedGoodCount() * $amount,
                $selectedOffer->getWantedGoodObject()->getName()
            ),
            PM_SPECIAL_TRADE
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}

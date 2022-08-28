<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TakeOffer;

use Stu\Exception\AccessViolation;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\Overview\Overview;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class TakeOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TAKE_OFFER';

    private TakeOfferRequestInterface $takeOfferRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TradeTransactionRepositoryInterface $tradeTransactionRepository;

    public function __construct(
        TakeOfferRequestInterface $takeOfferRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        TradeTransactionRepositoryInterface $tradeTransactionRepository
    ) {
        $this->takeOfferRequest = $takeOfferRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->tradeTransactionRepository = $tradeTransactionRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $offerId = $this->takeOfferRequest->getOfferId();
        $amount = $this->takeOfferRequest->getAmount();

        if ($amount < 1) {
            return;
        }

        $selectedOffer = $this->tradeOfferRepository->find($offerId);

        if ($selectedOffer === null) {
            $game->addInformation(_('Das Angebot ist nicht mehr verfügbar'));
            return;
        }

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $selectedOffer->getTradePost()->getId())) {
            throw new AccessViolation(sprintf(
                _('UserId %d does not have trade license on tradePostId %d'),
                $userId,
                $selectedOffer->getTradePost()->getId()
            ));
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
            $amount = (int) floor($storage->getAmount() / $selectedOffer->getWantedGoodCount());
        }
        if ($amount * $selectedOffer->getOfferedGoodCount() - $amount * $selectedOffer->getWantedGoodCount() > $freeStorage) {
            $amount = (int) floor($freeStorage / ($selectedOffer->getOfferedGoodCount() - $selectedOffer->getWantedGoodCount()));
            if ($amount <= 0) {
                $game->addInformation(_('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung'));
                return;
            }
        }

        if ($selectedOffer->getOfferCount() <= $amount) {
            $amount = $selectedOffer->getOfferCount();

            $this->tradeOfferRepository->delete($selectedOffer);
        } else {
            $selectedOffer->setOfferCount($selectedOffer->getOfferCount() - (int) $amount);

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

        $transaction = $this->tradeTransactionRepository->prototype();
        $transaction->setDate(time());
        $transaction->setWantedCommodity($selectedOffer->getWantedCommodity());
        $transaction->setWantedGoodCount($selectedOffer->getWantedGoodCount() * $amount);
        $transaction->setOfferedCommodity($selectedOffer->getOfferedCommodity());
        $transaction->setOfferedGoodCount($selectedOffer->getOfferedGoodCount() * $amount);
        $transaction->setTradePostId($selectedOffer->getTradePostId());
        $this->tradeTransactionRepository->save($transaction);

        $game->addInformation(sprintf(_('Das Angebot wurde %d mal angenommen'), $amount));

        $game->setView(Overview::VIEW_IDENTIFIER, ['FILTER_ACTIVE' => true]);

        $this->privateMessageSender->send(
            $userId,
            $selectedOffer->getUserId(),
            sprintf(
                'Am %s wurden insgesamt %d %s gegen %d %s getauscht',
                $selectedOffer->getTradePost()->getName(),
                $selectedOffer->getOfferedGoodCount() * $amount,
                $selectedOffer->getOfferedCommodity()->getName(),
                $selectedOffer->getWantedGoodCount() * $amount,
                $selectedOffer->getWantedCommodity()->getName()
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
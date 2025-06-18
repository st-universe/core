<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TakeOffer;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class TakeOffer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TAKE_OFFER';

    public function __construct(private TakeOfferRequestInterface $takeOfferRequest, private TradeLibFactoryInterface $tradeLibFactory, private TradeOfferRepositoryInterface $tradeOfferRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository, private PrivateMessageSenderInterface $privateMessageSender, private TradeTransactionRepositoryInterface $tradeTransactionRepository, private StorageRepositoryInterface $storageRepository) {}

    #[Override]
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

        if ($selectedOffer->getTradePost()->getUserId() === UserEnum::USER_NOONE) {
            $game->addInformation(_('Dieser Handelsposten wurde verlassen. Handel ist nicht mehr möglich.'));
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

        $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
            $selectedOffer->getTradePostId(),
            $userId,
            $selectedOffer->getWantedCommodityId()
        );

        if ($storage === null || $storage->getAmount() < $selectedOffer->getWantedCommodityCount()) {
            $game->addInformation(sprintf(
                _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                $selectedOffer->getWantedCommodity()->getName()
            ));
            return;
        }

        $tradePost = $storage->getTradePost();

        $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $game->getUser());
        $storageManagerRemote = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $selectedOffer->getUser());

        $freeStorage = $storageManagerUser->getFreeStorage();

        if (
            $freeStorage <= 0 &&
            $selectedOffer->getOfferedCommodityCount() > $selectedOffer->getWantedCommodityCount()
        ) {
            $game->addInformation(_('Dein Warenkonto auf diesem Handelsposten ist voll'));
            return;
        }
        if ($amount * $selectedOffer->getWantedCommodityCount() > $storage->getAmount()) {
            $amount = (int) floor($storage->getAmount() / $selectedOffer->getWantedCommodityCount());
        }
        if ($amount * $selectedOffer->getOfferedCommodityCount() - $amount * $selectedOffer->getWantedCommodityCount() > $freeStorage) {
            $amount = (int) floor($freeStorage / ($selectedOffer->getOfferedCommodityCount() - $selectedOffer->getWantedCommodityCount()));
            if ($amount <= 0) {
                $game->addInformation(_('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung'));
                return;
            }
        }

        if ($selectedOffer->getOfferCount() <= $amount) {
            $amount = $selectedOffer->getOfferCount();

            $this->storageRepository->delete($selectedOffer->getStorage());
            $this->tradeOfferRepository->delete($selectedOffer);
        } else {
            //modify offer
            $selectedOffer->setOfferCount($selectedOffer->getOfferCount() - $amount);
            $this->tradeOfferRepository->save($selectedOffer);

            //modify storage of offer
            $storage = $selectedOffer->getStorage();
            $storage->setAmount($selectedOffer->getOfferedCommodityCount() * $selectedOffer->getOfferCount());
            $this->storageRepository->save($storage);
        }

        $storageManagerRemote->upperStorage(
            $selectedOffer->getWantedCommodityId(),
            $selectedOffer->getWantedCommodityCount() * $amount
        );

        $storageManagerUser->upperStorage(
            $selectedOffer->getOfferedCommodityId(),
            $selectedOffer->getOfferedCommodityCount() * $amount
        );

        $storageManagerUser->lowerStorage(
            $selectedOffer->getWantedCommodityId(),
            $selectedOffer->getWantedCommodityCount() * $amount
        );

        $transaction = $this->tradeTransactionRepository->prototype();
        $transaction->setDate(time());
        $transaction->setWantedCommodity($selectedOffer->getWantedCommodity());
        $transaction->setWantedCommodityCount($selectedOffer->getWantedCommodityCount() * $amount);
        $transaction->setOfferedCommodity($selectedOffer->getOfferedCommodity());
        $transaction->setOfferedCommodityCount($selectedOffer->getOfferedCommodityCount() * $amount);
        $transaction->setTradePostId($selectedOffer->getTradePostId());
        $this->tradeTransactionRepository->save($transaction);

        $game->addInformation(sprintf(_('Das Angebot wurde %d mal angenommen'), $amount));

        $game->setView(ModuleEnum::TRADE);
        $game->setViewContext(ViewContextTypeEnum::FILTER_ACTIVE, true);

        $this->privateMessageSender->send(
            $userId,
            $selectedOffer->getUserId(),
            sprintf(
                'Am %s wurden insgesamt %d %s gegen %d %s getauscht',
                $selectedOffer->getTradePost()->getName(),
                $selectedOffer->getOfferedCommodityCount() * $amount,
                $selectedOffer->getOfferedCommodity()->getName(),
                $selectedOffer->getWantedCommodityCount() * $amount,
                $selectedOffer->getWantedCommodity()->getName()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}

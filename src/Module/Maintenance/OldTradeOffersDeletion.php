<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class OldTradeOffersDeletion implements MaintenanceHandlerInterface
{
    //two weeks
    public const OFFER_MAX_AGE = 1209600;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TradePostRepositoryInterface $tradePostRepository;

    public function __construct(
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        PrivateMessageSenderInterface $privateMessageSender,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->privateMessageSender = $privateMessageSender;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function handle(): void
    {
        $offersToDelete = $this->tradeOfferRepository->getOldOffers(OldTradeOffersDeletion::OFFER_MAX_AGE);

        $pm = [];
        $userId = 0;
        $postId = 0;

        foreach ($offersToDelete as $offer) {

            // send message to user
            if (!empty($pm) && $userId != $offer->getUserId()) {
                $this->sendMessage($userId, $pm);
                $pm = [];
                $userId = 0;
                $postId = 0;
            }

            // intro
            if (empty($pm)) {
                $pm[] = _('Deine folgenden Angebote wurden gelöscht und der Inhalt wieder deinen lagernden Waren zugeschrieben.');
            }

            //trade post change
            if ($postId != $offer->getTradePostId()) {
                $post = $this->tradePostRepository->find($offer->getTradePostId());
                $pm[] = sprintf(_('\n%s:'), $post->getName());
            }
            $userId = $offer->getUserId();
            $postId = $offer->getTradePostId();

            $offeredCount = (int) $offer->getOfferedGoodCount() * $offer->getOfferCount();
            $wantedCount = (int) $offer->getWantedGoodCount() * $offer->getOfferCount();
            $pm[] = sprintf(
                _('angeboten: %d %s, verlangt: %d %s'),
                $offeredCount,
                $offer->getOfferedCommodity()->getName(),
                $wantedCount,
                $offer->getWantedCommodity()->getName()
            );

            // update post storage
            $this->tradeLibFactory->createTradePostStorageManager(
                $offer->getTradePost(),
                $offer->getUserId(),
            )->upperStorage(
                (int) $offer->getOfferedGoodId(),
                $offeredCount
            );

            $this->tradeOfferRepository->delete($offer);
        }

        if (!empty($pm)) {
            $this->sendMessage($userId, $pm);
        }
    }

    private function sendMessage(int $userId, array $pmArray)
    {
        foreach ($pmArray as $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            $userId,
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
        );
    }
}

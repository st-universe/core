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
                $storageManager = $this->tradeLibFactory->createTradePostStorageManager(
                    $post,
                    $offer->getUserId(),
                );
                $pm[] = "\n" . sprintf(_('%s:'), $post->getName());
            }
            $userId = $offer->getUserId();
            $postId = $offer->getTradePostId();

            $pm[] = sprintf(
                _('%d x angeboten: %d %s, verlangt: %d %s'),
                $offer->getOfferCount(),
                $offer->getOfferedGoodCount(),
                $offer->getOfferedCommodity()->getName(),
                $offer->getWantedGoodCount(),
                $offer->getWantedCommodity()->getName()
            );

            // update post storage
            $storageManager->upperStorage(
                (int) $offer->getOfferedGoodId(),
                (int) $offer->getOfferedGoodCount() * $offer->getOfferCount()
            );

            $this->tradeOfferRepository->delete($offer);
        }

        if (!empty($pm)) {
            $this->sendMessage($userId, $pm);
        }
    }

    private function sendMessage(int $userId, array $pmArray)
    {
        $pm = '';

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

<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\TimeConstants;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class OldTradeOffersDeletion implements MaintenanceHandlerInterface
{
    public const OFFER_MAX_AGE = TimeConstants::TWO_WEEKS_IN_SECONDS;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private PrivateMessageSenderInterface $privateMessageSender;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        PrivateMessageSenderInterface $privateMessageSender,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->privateMessageSender = $privateMessageSender;
        $this->storageRepository = $storageRepository;
    }

    public function handle(): void
    {
        $offersToDelete = $this->tradeOfferRepository->getOldOffers(OldTradeOffersDeletion::OFFER_MAX_AGE);

        $pm = new InformationWrapper();
        $userId = 0;
        $postId = 0;

        foreach ($offersToDelete as $offer) {
            // send message to user
            if (!$pm->isEmpty() && $userId !== $offer->getUserId()) {
                $this->sendMessage($userId, $pm);
                $pm = new InformationWrapper();
                $userId = 0;
                $postId = 0;
            }

            // intro
            if ($pm->isEmpty()) {
                $pm->addInformation(_('Deine folgenden Angebote wurden gelöscht und der Inhalt wieder deinen lagernden Waren zugeschrieben.'));
            }

            //trade post change
            if ($postId !== $offer->getTradePostId()) {
                $post = $offer->getTradePost();
                $storageManager = $this->tradeLibFactory->createTradePostStorageManager(
                    $post,
                    $offer->getUser(),
                );
                $pm->addInformation("\n" . sprintf(_('%s:'), $post->getName()));
            }
            $userId = $offer->getUserId();
            $postId = $offer->getTradePostId();

            $pm->addInformation(sprintf(
                _('%d x angeboten: %d %s, verlangt: %d %s'),
                $offer->getOfferCount(),
                $offer->getOfferedCommodityCount(),
                $offer->getOfferedCommodity()->getName(),
                $offer->getWantedCommodityCount(),
                $offer->getWantedCommodity()->getName()
            ));

            // update post storage
            $storageManager->upperStorage(
                $offer->getOfferedCommodityId(),
                $offer->getOfferedCommodityCount() * $offer->getOfferCount()
            );

            $this->storageRepository->delete($offer->getStorage());
            $this->tradeOfferRepository->delete($offer);
        }

        if (!$pm->isEmpty()) {
            $this->sendMessage($userId, $pm);
        }
    }

    private function sendMessage(int $userId, InformationWrapper $pm): void
    {
        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $userId,
            $pm->getInformationsAsString(),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE
        );
    }
}

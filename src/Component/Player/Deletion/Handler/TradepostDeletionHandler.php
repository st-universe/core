<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TradepostDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private TradePostRepositoryInterface $tradePostRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private UserRepositoryInterface $userRepository,
        private StorageRepositoryInterface $storageRepository,
        private EntryCreatorInterface $entryCreator,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function delete(User $user): void
    {
        $fallbackUser = $this->userRepository->getFallbackUser();

        foreach ($this->tradePostRepository->getByUser($user->getId()) as $tradepost) {
            $station = $tradepost->getStation();

            // send PMs to storage owners except tradepost owner
            foreach ($this->tradePostRepository->getUsersWithStorageOnTradepost($tradepost->getId()) as $user) {
                if ($user->getId() !== $tradepost->getUserId()) {
                    $this->privateMessageSender->send(
                        UserEnum::USER_NOONE,
                        $user->getId(),
                        sprintf(
                            'Der Handelsposten "%s" bei den Koordinaten %s wurde verlassen. Du solltest deine Waren hier schleunigst abholen, sonst gehen sie verloren.',
                            $tradepost->getName(),
                            $station->getSectorString()
                        )
                    );
                }
            }

            //create history entry
            $this->entryCreator->addEntry(
                'Der Handelsposten in Sektor ' . $station->getSectorString() . ' wurde verlassen.',
                UserEnum::USER_NOONE,
                $station
            );

            //transfer tradepost to noone user
            $tradepost->setUser($fallbackUser);
            $tradepost->setName('Verlassener Handelsposten');
            $tradepost->setDescription('Verlassener Handelsposten');
            $tradepost->setTradeNetwork(UserEnum::USER_NOONE);
            $this->tradePostRepository->save($tradepost);

            $station->setUser($fallbackUser);
            $station->setName('Verlassener Handelsposten');
            $station->getCondition()->setDisabled(true);
            $this->spacecraftRepository->save($station);

            //change torpedo owner
            if ($station->getTorpedoStorage() !== null) {
                $storage = $station->getTorpedoStorage()->getStorage();
                $storage->setUser($fallbackUser);
                $this->storageRepository->save($storage);
            }
        }
    }
}

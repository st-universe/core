<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TradepostDeletionHandler implements PlayerDeletionHandlerInterface
{
    private TradePostRepositoryInterface $tradePostRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private StorageRepositoryInterface $storageRepository;

    private EntryCreatorInterface $entryCreator;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        TradePostRepositoryInterface $tradePostRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        StorageRepositoryInterface $storageRepository,
        EntryCreatorInterface $entryCreator,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->tradePostRepository = $tradePostRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->storageRepository = $storageRepository;
        $this->entryCreator = $entryCreator;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function delete(UserInterface $user): void
    {
        $fallbackUser = $this->userRepository->getFallbackUser();

        foreach ($this->tradePostRepository->getByUser($user->getId()) as $tradepost) {
            $ship = $tradepost->getShip();

            // send PMs to storage owners except tradepost owner
            foreach ($this->tradePostRepository->getUsersWithStorageOnTradepost($tradepost->getId()) as $user) {
                if ($user->getId() !== $tradepost->getUserId()) {
                    $this->privateMessageSender->send(
                        UserEnum::USER_NOONE,
                        $user->getId(),
                        sprintf(
                            'Der Handelsposten "%s" bei den Koordinaten %s wurde verlassen. Du solltest deine Waren hier schleunigst abholen, sonst gehen sie verloren.',
                            $tradepost->getName(),
                            $ship->getSectorString()
                        )
                    );
                }
            }

            //create history entry
            $this->entryCreator->addStationEntry(
                'Der Handelsposten in Sektor ' . $ship->getSectorString() . ' wurde verlassen.',
                $ship->getUser()->getId(),
                UserEnum::USER_NOONE
            );

            //transfer tradepost to noone user
            $tradepost->setUser($fallbackUser);
            $tradepost->setName('Verlassener Handelsposten');
            $tradepost->setDescription('Verlassener Handelsposten');
            $tradepost->setTradeNetwork(UserEnum::USER_NOONE);
            $this->tradePostRepository->save($tradepost);

            $ship->setUser($fallbackUser);
            $ship->setName('Verlassener Handelsposten');
            $ship->setDisabled(true);
            $this->shipRepository->save($ship);

            //change torpedo owner
            if ($ship->getTorpedoStorage() !== null) {
                $storage = $ship->getTorpedoStorage()->getStorage();
                $storage->setUser($fallbackUser);
                $this->storageRepository->save($storage);
            }
        }
    }
}
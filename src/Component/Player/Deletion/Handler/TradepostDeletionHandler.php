<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Component\Game\GameEnum;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TradepostDeletionHandler implements PlayerDeletionHandlerInterface
{
    private TradePostRepositoryInterface $tradePostRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private EntryCreatorInterface $entryCreator;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        TradePostRepositoryInterface $tradePostRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        EntryCreatorInterface $entryCreator,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->tradePostRepository = $tradePostRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->entryCreator = $entryCreator;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function delete(UserInterface $user): void
    {
        foreach ($this->tradePostRepository->getByUser($user->getId()) as $tradepost) {
            $ship = $tradepost->getShip();

            // send PMs to license owners except tradepost owner
            foreach ($this->tradePostRepository->getUsersWithStorageOnTradepost($tradepost->getId()) as $user) {
                if ($user->getId() !== $tradepost->getUserId()) {
                    $this->privateMessageSender->send(
                        GameEnum::USER_NOONE,
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
                $ship->getUser()->getId()
            );

            $noOne = $this->userRepository->find(GameEnum::USER_NOONE);

            //transfer tradepost to noone user
            $tradepost->setUser($noOne);
            $tradepost->setName('Verlassener Handelsposten');
            $tradepost->setDescription('Verlassener Handelsposten');
            $tradepost->setTradeNetwork(GameEnum::USER_NOONE);
            $this->tradePostRepository->save($tradepost);

            $ship->setUser($noOne);
            $ship->setName('Verlassener Handelsposten');
            $ship->setDisabled(true);
            $this->shipRepository->save($ship);
        }
    }
}

<?php

namespace Stu\Module\Game\Lib\View\Provider\Message;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

class MessengerStyleProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private PrivateMessageRepositoryInterface $privateMessageRepository,
        private ContactRepositoryInterface $contactRepository,
        private StuTime $stuTime
    ) {}

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $messages = $this->privateMessageRepository->getConversations($user);
        $timestamp = $this->stuTime->time();

        $conversations = [];

        foreach ($messages as $message) {
            $senderId = $message->getSenderId();
            if (!array_key_exists($senderId, $conversations)) {
                $conversations[$senderId] = new Conversation(
                    $message,
                    $timestamp,
                    $this->stuTime,
                    $user->getId(),
                    $this->privateMessageRepository,
                    $this->contactRepository
                );
            }
        }

        $game->setTemplateVar('CONVERSATIONS', $conversations);
    }
}

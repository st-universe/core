<?php

namespace Stu\Module\Game\Lib\View\Provider\Message;

use Stu\Component\Game\TimeConstants;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Orm\Entity\PrivateMessageInterface;
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
                    $this->determineUnreadPmCount($message),
                    $this->determineDateString($message, $timestamp),
                    $user,
                    $this->privateMessageRepository,
                    $this->contactRepository
                );
            }
        }

        $game->setTemplateVar('CONVERSATIONS', $conversations);
    }

    private function determineUnreadPmCount(PrivateMessageInterface $message): int
    {
        return $this->privateMessageRepository->getNewAmountByFolderAndSender(
            $message->getCategory(),
            $message->getSender()
        );
    }

    private function determineDateString(PrivateMessageInterface $message, int $timestamp): string
    {
        $messageTimestamp = $message->getDate();
        $distanceInSeconds = $timestamp - $messageTimestamp;

        if ($distanceInSeconds < TimeConstants::ONE_DAY_IN_SECONDS) {
            return date("H:i", $messageTimestamp);
        }
        if ($distanceInSeconds < TimeConstants::SEVEN_DAYS_IN_SECONDS) {
            return match ((int)date("N", $messageTimestamp)) {
                1 => 'Montag',
                2 => 'Dienstag',
                3 => 'Mittwoch',
                4 => 'Donnerstag',
                5 => 'Freitag',
                6 => 'Samstag',
                7 => 'Sonntag'
            };
        }
        return $this->stuTime->transformToStuDate($messageTimestamp);
    }
}

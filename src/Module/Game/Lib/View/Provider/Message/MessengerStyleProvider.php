<?php

namespace Stu\Module\Game\Lib\View\Provider\Message;

use RuntimeException;
use request;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Paging\PagingFactory;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

class MessengerStyleProvider implements ViewComponentProviderInterface
{
    private const int CONVERSATION_LIMIT = 20;

    public function __construct(
        private readonly PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private readonly PrivateMessageRepositoryInterface $privateMessageRepository,
        private readonly ContactRepositoryInterface $contactRepository,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly PagingFactory $pagingFactory,
        private readonly StuTime $stuTime
    ) {}

    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $category = $this->privateMessageFolderRepository->getByUserAndSpecial(
            $user->getId(),
            PrivateMessageFolderTypeEnum::SPECIAL_MAIN
        );
        if ($category === null) {
            throw new RuntimeException('main PM category not found');
        }
            
        $mark = request::indInt('mark');
        if ($mark < 0 || $mark % self::CONVERSATION_LIMIT !== 0) {
            $mark = 0;
        }

        $conversationPage = $this->privateMessageRepository->getConversations(
            $user,
            $mark,
            self::CONVERSATION_LIMIT
        );
        /** @var array<int, PrivateMessage> $messages */
        $messages = $conversationPage['items'];
        $timestamp = $this->stuTime->time();

        $conversations = [];

        foreach ($messages as $message) {
            $sender = $message->getSender();
            $senderId = $sender->getId();
            $formerSenderId = $message->getFormerSendUser();

            if ($senderId === 1) {
                if ($formerSenderId === null || $formerSenderId === 1) {
                    continue;
                }

                $groupId = $formerSenderId;
            } else {
                $groupId = $senderId;
            }
            if (!array_key_exists($groupId, $conversations)) {
                $conversations[$groupId] = new Conversation(
                    $message,
                    $this->determineUnreadPmCount($message),
                    $this->determineDateString($message, $timestamp),
                    $user,
                    $this->privateMessageRepository,
                    $this->contactRepository,
                    $this->userSettingsProvider
                );
            }
        }

        $game->setTemplateVar('CONVERSATIONS', $conversations);
        $game->setTemplateVar('PAGING', $this->pagingFactory->createPaging(
            $conversationPage['total'],
            self::CONVERSATION_LIMIT,
            $mark
        ));
    }

    private function determineUnreadPmCount(PrivateMessage $message): int
    {
        return $this->privateMessageRepository->getNewAmountByFolderAndSender(
            $message->getCategory(),
            $message->getSender()
        );
    }

    private function determineDateString(PrivateMessage $message, int $timestamp): string
    {
        $messageTimestamp = $message->getDate();
        $distanceInSeconds = $timestamp - $messageTimestamp;

        if ($distanceInSeconds < TimeConstants::ONE_DAY_IN_SECONDS) {
            return date('H:i', $messageTimestamp);
        }
        if ($distanceInSeconds < TimeConstants::SEVEN_DAYS_IN_SECONDS) {
            return match ((int) date('N', $messageTimestamp)) {
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

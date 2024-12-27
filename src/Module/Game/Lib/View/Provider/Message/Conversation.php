<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider\Message;

use Stu\Component\Game\TimeConstants;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageListItem;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class Conversation extends PrivateMessageListItem
{
    public function __construct(
        private PrivateMessageInterface $message,
        private int $time,
        private StuTime $stuTime,
        int $currentUserId,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        ContactRepositoryInterface $contactRepository
    ) {
        parent::__construct(
            $privateMessageRepository,
            $contactRepository,
            $message,
            $currentUserId
        );
    }

    public function getLastHeadline(): string
    {
        return sprintf(
            '%s%s',
            $this->message->getInboxPm() === null ? sprintf(
                '%s: ',
                $this->getOtherUser()->getName()
            ) : '',
            substr($this->message->getText(), 0, 200)
        );
    }

    public function getOtherUser(): UserInterface
    {
        return $this->message->getSender();
    }

    public function getDateString(): string
    {
        //TODO move code to MessengerStyleProvider and inject as constructor parameter
        $messageTimestamp = $this->message->getDate();
        $distanceInSeconds = $this->time - $messageTimestamp;

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

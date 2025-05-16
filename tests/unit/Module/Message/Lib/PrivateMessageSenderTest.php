<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Lib\General\EntityWithHrefInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class PrivateMessageSenderTest extends StuTestCase
{
    /** @var MockInterface&PrivateMessageFolderRepositoryInterface */
    private $messageFolderRepository;
    /** @var MockInterface&PrivateMessageRepositoryInterface */
    private $messageRepository;
    /** @var MockInterface&UserRepositoryInterface */
    private $userRepository;
    /** @var MockInterface&EmailNotificationSenderInterface */
    private $emailNotificationSender;
    /** @var MockInterface&StuTime */
    private $stuTime;

    private PrivateMessageSenderInterface $messageSender;

    #[Override]
    public function setUp(): void
    {
        $this->messageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $this->messageRepository = $this->mock(PrivateMessageRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->emailNotificationSender = $this->mock(EmailNotificationSenderInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->messageSender = new PrivateMessageSender(
            $this->messageFolderRepository,
            $this->messageRepository,
            $this->userRepository,
            $this->emailNotificationSender,
            $this->stuTime
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
        PrivateMessageSender::$blockedUserIds = [];
    }

    public function testSendWithoutEmailNotification(): void
    {
        $sender = $this->mock(UserInterface::class);
        $recipient = $this->mock(UserInterface::class);
        $hrefEntity = $this->mock(EntityWithHrefInterface::class);

        $recipientfolder = $this->mock(PrivateMessageFolderInterface::class);
        $senderOutboxFolder = $this->mock(PrivateMessageFolderInterface::class);

        $recipientpm = $this->mock(PrivateMessageInterface::class);
        $outboxPm = $this->mock(PrivateMessageInterface::class);

        $sender->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $sender->shouldReceive('isContactable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $recipient->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $this->userRepository->shouldReceive('find')
            ->with(2)
            ->once()
            ->andReturn($sender);
        $this->userRepository->shouldReceive('find')
            ->with(3)
            ->once()
            ->andReturn($recipient);

        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(3, PrivateMessageFolderTypeEnum::SPECIAL_STATION)
            ->once()
            ->andReturn($recipientfolder);
        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(2, PrivateMessageFolderTypeEnum::SPECIAL_PMOUT)
            ->once()
            ->andReturn($senderOutboxFolder);

        $this->messageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->times(2)
            ->andReturn($recipientpm, $outboxPm);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $recipientpm->shouldReceive('setDate')
            ->with(42)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setDate')
            ->with(42)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setCategory')
            ->with($recipientfolder)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setCategory')
            ->with($senderOutboxFolder)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setText')
            ->with('foobar')
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setText')
            ->with('foobar')
            ->once()
            ->andReturnSelf();

        $hrefEntity->shouldReceive('getHref')
            ->withNoArgs()
            ->once()
            ->andReturn('href');

        $recipientpm->shouldReceive('setHref')
            ->with('href')
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setHref')
            ->with(null)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setRecipient')
            ->with($recipient)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setRecipient')
            ->with($sender)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setSender')
            ->with($sender)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setSender')
            ->with($recipient)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setNew')
            ->with(true)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setNew')
            ->with(false)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setInboxPm')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setInboxPm')
            ->with($recipientpm)
            ->once()
            ->andReturnSelf();

        $this->messageRepository->shouldReceive('save')
            ->with($recipientpm)
            ->once();
        $this->messageRepository->shouldReceive('save')
            ->with($outboxPm)
            ->once();

        $this->messageSender->send(2, 3, 'foobar', PrivateMessageFolderTypeEnum::SPECIAL_STATION, $hrefEntity);
    }

    public function testSendWithEmailNotificationAndAlreadyRead(): void
    {
        $sender = $this->mock(UserInterface::class);
        $recipient = $this->mock(UserInterface::class);

        $recipientfolder = $this->mock(PrivateMessageFolderInterface::class);
        $senderOutboxFolder = $this->mock(PrivateMessageFolderInterface::class);

        $recipientpm = $this->mock(PrivateMessageInterface::class);
        $outboxPm = $this->mock(PrivateMessageInterface::class);

        $sender->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $sender->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("[b]SENDER[/b]");
        $sender->shouldReceive('isContactable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $recipient->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);
        $recipient->shouldReceive('isEmailNotification')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->userRepository->shouldReceive('find')
            ->with(2)
            ->once()
            ->andReturn($sender);
        $this->userRepository->shouldReceive('find')
            ->with(3)
            ->once()
            ->andReturn($recipient);

        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(3, PrivateMessageFolderTypeEnum::SPECIAL_MAIN)
            ->once()
            ->andReturn($recipientfolder);
        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(2, PrivateMessageFolderTypeEnum::SPECIAL_PMOUT)
            ->once()
            ->andReturn($senderOutboxFolder);

        $this->messageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->times(2)
            ->andReturn($recipientpm, $outboxPm);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $recipientpm->shouldReceive('setDate')
            ->with(42)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setDate')
            ->with(42)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setCategory')
            ->with($recipientfolder)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setCategory')
            ->with($senderOutboxFolder)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setText')
            ->with('foobar')
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setText')
            ->with('foobar')
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setHref')
            ->with('href')
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setHref')
            ->with(null)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setRecipient')
            ->with($recipient)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setRecipient')
            ->with($sender)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setSender')
            ->with($sender)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setSender')
            ->with($recipient)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setNew')
            ->with(false)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setNew')
            ->with(false)
            ->once()
            ->andReturnSelf();

        $recipientpm->shouldReceive('setInboxPm')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setInboxPm')
            ->with($recipientpm)
            ->once()
            ->andReturnSelf();

        $this->emailNotificationSender->shouldReceive('sendNotification')
            ->with('[b]SENDER[/b]', 'foobar', $recipient)
            ->once();

        $this->messageRepository->shouldReceive('save')
            ->with($recipientpm)
            ->once();
        $this->messageRepository->shouldReceive('save')
            ->with($outboxPm)
            ->once();

        $this->messageSender->send(2, 3, 'foobar', PrivateMessageFolderTypeEnum::SPECIAL_MAIN, 'href', true);
    }

    public function testSendBroadcastWithEmptyRecipients(): void
    {
        $sender = $this->mock(UserInterface::class);

        $this->messageRepository->shouldNotHaveBeenCalled();

        $this->messageSender->sendBroadcast($sender, [], '');
    }

    public function testSendBroadcastWithRecipients(): void
    {
        $sender = $this->mock(UserInterface::class);
        $recipient1 = $this->mock(UserInterface::class);
        $recipient2 = $this->mock(UserInterface::class);
        $userNoOne = $this->mock(UserInterface::class);

        $recipient1folder = $this->mock(PrivateMessageFolderInterface::class);
        $recipient2folder = $this->mock(PrivateMessageFolderInterface::class);
        $senderOutboxFolder = $this->mock(PrivateMessageFolderInterface::class);

        $recipient1pm = $this->mock(PrivateMessageInterface::class);
        $recipient2pm = $this->mock(PrivateMessageInterface::class);
        $outboxPm = $this->mock(PrivateMessageInterface::class);


        $sender->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(55);
        $recipient1->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $recipient2->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $recipient1->shouldReceive('isEmailNotification')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $recipient2->shouldReceive('isEmailNotification')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $sender->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('[b]SENDER[/b]');

        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(1, PrivateMessageFolderTypeEnum::SPECIAL_MAIN)
            ->once()
            ->andReturn($recipient1folder);
        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(2, PrivateMessageFolderTypeEnum::SPECIAL_MAIN)
            ->once()
            ->andReturn($recipient2folder);
        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(55, PrivateMessageFolderTypeEnum::SPECIAL_PMOUT)
            ->once()
            ->andReturn($senderOutboxFolder);

        $this->messageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->times(3)
            ->andReturn($recipient1pm, $recipient2pm, $outboxPm);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $recipient1pm->shouldReceive('setDate')
            ->with(42)
            ->once()
            ->andReturnSelf();
        $recipient2pm->shouldReceive('setDate')
            ->with(42)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setDate')
            ->with(42)
            ->once()
            ->andReturnSelf();

        $recipient1pm->shouldReceive('setCategory')
            ->with($recipient1folder)
            ->once()
            ->andReturnSelf();
        $recipient2pm->shouldReceive('setCategory')
            ->with($recipient2folder)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setCategory')
            ->with($senderOutboxFolder)
            ->once()
            ->andReturnSelf();

        $recipient1pm->shouldReceive('setText')
            ->with('foobar')
            ->once()
            ->andReturnSelf();
        $recipient2pm->shouldReceive('setText')
            ->with('foobar')
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setText')
            ->with('foobar')
            ->once()
            ->andReturnSelf();

        $recipient1pm->shouldReceive('setHref')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $recipient2pm->shouldReceive('setHref')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setHref')
            ->with(null)
            ->once()
            ->andReturnSelf();

        $recipient1pm->shouldReceive('setRecipient')
            ->with($recipient1)
            ->once()
            ->andReturnSelf();
        $recipient2pm->shouldReceive('setRecipient')
            ->with($recipient2)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setRecipient')
            ->with($sender)
            ->once()
            ->andReturnSelf();

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($userNoOne);

        $recipient1pm->shouldReceive('setSender')
            ->with($sender)
            ->once()
            ->andReturnSelf();
        $recipient2pm->shouldReceive('setSender')
            ->with($sender)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setSender')
            ->with($userNoOne)
            ->once()
            ->andReturnSelf();

        $recipient1pm->shouldReceive('setNew')
            ->with(true)
            ->once()
            ->andReturnSelf();
        $recipient2pm->shouldReceive('setNew')
            ->with(true)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setNew')
            ->with(false)
            ->once()
            ->andReturnSelf();

        $recipient1pm->shouldReceive('setInboxPm')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $recipient2pm->shouldReceive('setInboxPm')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $outboxPm->shouldReceive('setInboxPm')
            ->with(null)
            ->once()
            ->andReturnSelf();

        $this->messageRepository->shouldReceive('save')
            ->with($recipient1pm)
            ->once();
        $this->messageRepository->shouldReceive('save')
            ->with($recipient2pm)
            ->once();
        $this->messageRepository->shouldReceive('save')
            ->with($outboxPm)
            ->once();

        $this->emailNotificationSender->shouldReceive('sendNotification')
            ->with('[b]SENDER[/b]', 'foobar', $recipient1)
            ->once();

        $this->messageSender->sendBroadcast($sender, [$recipient1, $recipient2], 'foobar');
    }

    public function testSendExpectNothingWhenRecipientIsBlocked(): void
    {
        $this->messageRepository->shouldNotHaveBeenCalled();
        $this->messageFolderRepository->shouldNotHaveBeenCalled();
        $this->userRepository->shouldNotHaveBeenCalled();

        PrivateMessageSender::$blockedUserIds = [3];

        $this->messageSender->send(2, 3, 'foobar');
    }

    public function testSendExpectNoOutboxWhenSenderIsBlocked(): void
    {
        $fallbackUser = $this->mock(UserInterface::class);
        $recipient = $this->mock(UserInterface::class);
        $folder = $this->mock(PrivateMessageFolderInterface::class);
        $message = $this->mock(PrivateMessageInterface::class);

        PrivateMessageSender::$blockedUserIds = [2];

        $fallbackUser->shouldReceive('isContactable')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $recipient->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($fallbackUser);
        $this->userRepository->shouldReceive('find')
            ->with(3)
            ->once()
            ->andReturn($recipient);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(424242);

        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(3, PrivateMessageFolderTypeEnum::SPECIAL_STATION)
            ->once()
            ->andReturn($folder);

        $this->messageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($message);
        $this->messageRepository->shouldReceive('save')
            ->with($message)
            ->once();

        $message->shouldReceive('setDate')
            ->with(424242)
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('setCategory')
            ->with($folder)
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('setText')
            ->with('foobar')
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('setHref')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('setRecipient')
            ->with($recipient)
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('setSender')
            ->with($fallbackUser)
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('setNew')
            ->with(true)
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('setInboxPm')
            ->with(null)
            ->once()
            ->andReturnSelf();

        $this->messageSender->send(2, 3, 'foobar', PrivateMessageFolderTypeEnum::SPECIAL_STATION);
    }
}

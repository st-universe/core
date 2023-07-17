<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Doctrine\ORM\EntityManager;
use JBBCode\Parser;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class PrivateMessageSenderTest extends StuTestCase
{
    /**
     * @var MockInterface|PrivateMessageFolderRepositoryInterface
     */
    private PrivateMessageFolderRepositoryInterface $messageFolderRepository;

    /**
     * @var MockInterface|PrivateMessageRepositoryInterface
     */
    private PrivateMessageRepositoryInterface $messageRepository;

    private UserRepositoryInterface $userRepository;

    private ConfigInterface $config;

    private Parser $parser;

    private StuTime $stuTime;

    private EntityManager $entityManager;

    private PrivateMessageSenderInterface $messageSender;

    public function setUp(): void
    {
        $this->messageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $this->messageRepository = $this->mock(PrivateMessageRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->config = $this->mock(ConfigInterface::class);
        $this->parser = $this->mock(Parser::class);
        $this->stuTime = $this->mock(StuTime::class);
        $this->entityManager = $this->mock(EntityManager::class);

        $loggerUtil = $this->mock(LoggerUtilInterface::class);
        $loggerUtilFactory = $this->mock(LoggerUtilFactoryInterface::class);

        $loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->withNoArgs()
            ->once()
            ->andReturn($loggerUtil);
        $loggerUtil->shouldReceive('log')
            ->withSomeOfArgs()
            ->zeroOrMoreTimes();

        $this->messageSender = new PrivateMessageSender(
            $this->messageFolderRepository,
            $this->messageRepository,
            $this->userRepository,
            $this->config,
            $this->parser,
            $this->stuTime,
            $this->entityManager,
            $loggerUtilFactory
        );
    }

    public function testSendWithoutEmailNotification(): void
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
            ->with(3, PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION)
            ->once()
            ->andReturn($recipientfolder);
        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(2, PrivateMessageFolderSpecialEnum::PM_SPECIAL_PMOUT)
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
            ->once();
        $outboxPm->shouldReceive('setDate')
            ->with(42)
            ->once();

        $recipientpm->shouldReceive('setCategory')
            ->with($recipientfolder)
            ->once();
        $outboxPm->shouldReceive('setCategory')
            ->with($senderOutboxFolder)
            ->once();

        $recipientpm->shouldReceive('setText')
            ->with('foobar')
            ->once();
        $outboxPm->shouldReceive('setText')
            ->with('foobar')
            ->once();

        $recipientpm->shouldReceive('setHref')
            ->with('href')
            ->once();
        $outboxPm->shouldReceive('setHref')
            ->with(null)
            ->once();

        $recipientpm->shouldReceive('setRecipient')
            ->with($recipient)
            ->once();
        $outboxPm->shouldReceive('setRecipient')
            ->with($sender)
            ->once();

        $recipientpm->shouldReceive('setSender')
            ->with($sender)
            ->once();
        $outboxPm->shouldReceive('setSender')
            ->with($recipient)
            ->once();

        $recipientpm->shouldReceive('setNew')
            ->with(true)
            ->once();
        $outboxPm->shouldReceive('setNew')
            ->with(false)
            ->once();

        $recipientpm->shouldReceive('getId')
            ->withNoArgs()
            ->once()->andReturn(123);
        $recipientpm->shouldReceive('setInboxPmId')
            ->with(null)
            ->once();
        $outboxPm->shouldReceive('setInboxPmId')
            ->with(123)
            ->once();

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->messageRepository->shouldReceive('save')
            ->with($recipientpm)
            ->once();
        $this->messageRepository->shouldReceive('save')
            ->with($outboxPm)
            ->once();

        $this->messageSender->send(2, 3, 'foobar', PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION, 'href');
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

        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(1, PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN)
            ->once()
            ->andReturn($recipient1folder);
        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(2, PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN)
            ->once()
            ->andReturn($recipient2folder);
        $this->messageFolderRepository->shouldReceive('getByUserAndSpecial')
            ->with(55, PrivateMessageFolderSpecialEnum::PM_SPECIAL_PMOUT)
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
            ->once();
        $recipient2pm->shouldReceive('setDate')
            ->with(42)
            ->once();
        $outboxPm->shouldReceive('setDate')
            ->with(42)
            ->once();

        $recipient1pm->shouldReceive('setCategory')
            ->with($recipient1folder)
            ->once();
        $recipient2pm->shouldReceive('setCategory')
            ->with($recipient2folder)
            ->once();
        $outboxPm->shouldReceive('setCategory')
            ->with($senderOutboxFolder)
            ->once();

        $recipient1pm->shouldReceive('setText')
            ->with('foobar')
            ->once();
        $recipient2pm->shouldReceive('setText')
            ->with('foobar')
            ->once();
        $outboxPm->shouldReceive('setText')
            ->with('foobar')
            ->once();

        $recipient1pm->shouldReceive('setHref')
            ->with(null)
            ->once();
        $recipient2pm->shouldReceive('setHref')
            ->with(null)
            ->once();
        $outboxPm->shouldReceive('setHref')
            ->with(null)
            ->once();

        $recipient1pm->shouldReceive('setRecipient')
            ->with($recipient1)
            ->once();
        $recipient2pm->shouldReceive('setRecipient')
            ->with($recipient2)
            ->once();
        $outboxPm->shouldReceive('setRecipient')
            ->with($sender)
            ->once();

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($userNoOne);

        $recipient1pm->shouldReceive('setSender')
            ->with($sender)
            ->once();
        $recipient2pm->shouldReceive('setSender')
            ->with($sender)
            ->once();
        $outboxPm->shouldReceive('setSender')
            ->with($userNoOne)
            ->once();

        $recipient1pm->shouldReceive('setNew')
            ->with(true)
            ->once();
        $recipient2pm->shouldReceive('setNew')
            ->with(true)
            ->once();
        $outboxPm->shouldReceive('setNew')
            ->with(false)
            ->once();

        $recipient1pm->shouldReceive('setInboxPmId')
            ->with(null)
            ->once();
        $recipient2pm->shouldReceive('setInboxPmId')
            ->with(null)
            ->once();
        $outboxPm->shouldReceive('setInboxPmId')
            ->with(null)
            ->once();

        $this->messageRepository->shouldReceive('save')
            ->with($recipient1pm)
            ->once();
        $this->messageRepository->shouldReceive('save')
            ->with($recipient2pm)
            ->once();
        $this->messageRepository->shouldReceive('save')
            ->with($outboxPm)
            ->once();

        $this->messageSender->sendBroadcast($sender, [$recipient1, $recipient2], 'foobar');
    }
}

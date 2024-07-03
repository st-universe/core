<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use JBBCode\Parser;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Lib\Mail\MailFactoryInterface;
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

    /** @var MockInterface|MailFactoryInterface */
    private $mailFactory;

    private ConfigInterface $config;

    private Parser $parser;

    private StuTime $stuTime;

    private PrivateMessageSenderInterface $messageSender;

    #[Override]
    public function setUp(): void
    {
        $this->messageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $this->messageRepository = $this->mock(PrivateMessageRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->mailFactory = $this->mock(MailFactoryInterface::class);
        $this->config = $this->mock(ConfigInterface::class);
        $this->parser = $this->mock(Parser::class);
        $this->stuTime = $this->mock(StuTime::class);

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
            $this->mailFactory,
            $this->config,
            $this->parser,
            $this->stuTime,
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

        $outboxPm->shouldReceive('setInboxPm')
            ->with($recipientpm)
            ->once();

        $this->messageRepository->shouldReceive('save')
            ->with($recipientpm)
            ->once();
        $this->messageRepository->shouldReceive('save')
            ->with($outboxPm)
            ->once();

        $this->messageSender->send(2, 3, 'foobar', PrivateMessageFolderTypeEnum::SPECIAL_STATION, 'href');
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

        $recipient->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);
        $recipient->shouldReceive('isEmailNotification')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $recipient->shouldReceive('getEmail')
            ->withNoArgs()
            ->once()
            ->andReturn("e@mail.de");

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
            ->with(false)
            ->once();
        $outboxPm->shouldReceive('setNew')
            ->with(false)
            ->once();

        $outboxPm->shouldReceive('setInboxPm')
            ->with($recipientpm)
            ->once();

        $parser = $this->mock(Parser::class);
        $this->parser->shouldReceive('parse')
            ->with('[b]SENDER[/b]')
            ->once()
            ->andReturn($parser);
        $parser->shouldReceive('getAsText')
            ->withNoArgs()
            ->once()
            ->andReturn('Sender');

        $this->config->shouldReceive('get')
            ->with('game.email_sender_address')
            ->once()
            ->andReturn('emai@sender.adress');

        $message = $this->mock(Message::class);
        $this->mailFactory->shouldReceive('createMessage')
            ->withNoArgs()
            ->once()
            ->andReturn($message);
        $sendmail = $this->mock(Sendmail::class);
        $this->mailFactory->shouldReceive('createSendmail')
            ->withNoArgs()
            ->once()
            ->andReturn($sendmail);

        $sendmail->shouldReceive('send')
            ->with($message)
            ->once();

        $message->shouldReceive('addTo')
            ->with('e@mail.de')
            ->once();
        $message->shouldReceive('setSubject')
            ->with('Neue Privatnachricht von Spieler Sender')
            ->once();
        $message->shouldReceive('setFrom')
            ->with('emai@sender.adress')
            ->once();
        $message->shouldReceive('setBody')
            ->with('foobar')
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

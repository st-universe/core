<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use JBBCode\Parser;
use Mockery\MockInterface;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Lib\Mail\StuMailInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class EmailNotificationSenderTest extends StuTestCase
{
    private $mailFactory;
    private MockInterface&Parser $parser;

    private EmailNotificationSenderInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->mailFactory = $this->mock(MailFactoryInterface::class);
        $this->parser = $this->mock(Parser::class);

        $loggerUtil = $this->mock(LoggerUtilInterface::class);
        $loggerUtilFactory = $this->mock(LoggerUtilFactoryInterface::class);

        $loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->withNoArgs()
            ->once()
            ->andReturn($loggerUtil);
        $loggerUtil->shouldReceive('log')
            ->withSomeOfArgs()
            ->zeroOrMoreTimes();

        $this->subject = new EmailNotificationSender(
            $this->mailFactory,
            $this->parser,
            $loggerUtilFactory
        );
    }

    public function testSendNotification(): void
    {
        $user = $this->mock(User::class);
        $parser = $this->mock(Parser::class);

        $user->shouldReceive('getRegistration->getEmail')
            ->withNoArgs()
            ->once()
            ->andReturn('e@mail.de');

        $this->parser->shouldReceive('parse')
            ->with('[b]SENDER[/b]')
            ->once()
            ->andReturn($parser);
        $parser->shouldReceive('getAsText')
            ->withNoArgs()
            ->once()
            ->andReturn('Sender');

        $message = $this->mock(StuMailInterface::class);
        $this->mailFactory->shouldReceive('createStuMail')
            ->withNoArgs()
            ->once()
            ->andReturn($message);

        $message->shouldReceive('addTo')
            ->with('e@mail.de')
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('setSubject')
            ->with('Neue Privatnachricht von Spieler Sender')
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('withDefaultSender')
            ->withNoArgs()
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('setBody')
            ->with('foobar')
            ->once()
            ->andReturnSelf();
        $message->shouldReceive('send')
            ->withNoArgs()
            ->once();

        $this->subject->sendNotification('[b]SENDER[/b]', 'foobar', $user);
    }
}

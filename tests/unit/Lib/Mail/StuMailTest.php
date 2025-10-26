<?php

declare(strict_types=1);

namespace Stu\Lib\Mail;

use Mockery\MockInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\StuTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class StuMailTest extends StuTestCase
{
    private MockInterface&Email $email;
    private MockInterface&MailerInterface $mailer;
    private MockInterface&StuConfigInterface $stuConfig;

    private StuMailInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->email = $this->mock(Email::class);
        $this->mailer = $this->mock(MailerInterface::class);
        $this->stuConfig = $this->mock(StuConfigInterface::class);

        $this->subject = new StuMail(
            $this->email,
            $this->mailer,
            $this->stuConfig
        );
    }

    public function testWithDefaultSender(): void
    {
        $this->stuConfig->shouldReceive(
            'getGameSettings->getEmailSettings->getSenderAddress'
        )
            ->withNoArgs()
            ->once()
            ->andReturn('SEN@D.ER');

        $this->email->shouldReceive('from')
            ->with('SEN@D.ER')
            ->once();

        $result = $this->subject->withDefaultSender();

        $this->assertSame($this->subject, $result);
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Message;

use Stu\StuTestCase;

class MessageFactoryTest extends StuTestCase
{
    private MessageFactoryInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->subject = new MessageFactory();
    }

    public function testCreateMessage(): void
    {
        $senderId = 5;
        $recipientId = 42;

        $result = $this->subject->createMessage(
            $senderId,
            $recipientId,
            ['foo']
        );

        $msg = $result->getMessage();

        $this->assertEquals(['foo'], $msg);
    }
}

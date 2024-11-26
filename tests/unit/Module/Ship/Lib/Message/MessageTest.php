<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Message;

use Override;
use Stu\StuTestCase;

class MessageTest extends StuTestCase
{
    private int $senderId = 5;
    private int $recipientId = 42;

    private MessageInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->subject = new Message(
            $this->senderId,
            $this->recipientId
        );
    }

    public function testGetSenderId(): void
    {
        $result = $this->subject->getSenderId();

        $this->assertEquals($this->senderId, $result);
    }

    public function testGetRecipientId(): void
    {
        $result = $this->subject->getRecipientId();

        $this->assertEquals($this->recipientId, $result);
    }

    public function testAddExpectNothingWhenNull(): void
    {
        $this->subject->add(null);
        $msg = $this->subject->getMessage();

        $this->assertEmpty($msg);
    }

    public function testAddExpectAddingWhenNotNull(): void
    {
        $this->subject->add('foo');
        $this->subject->add('test');
        $msg = $this->subject->getMessage();

        $this->assertEquals(['foo', 'test'], $msg);
    }

    public function testAddMessageMergeExpectNothingWhenEmpty(): void
    {
        $this->subject->addMessageMerge([]);
        $msg = $this->subject->getMessage();

        $this->assertEmpty($msg);
    }

    public function testAddMessageMergeExpectAddingWhenNotEmpty(): void
    {
        $this->subject->addMessageMerge(['foo', 'test']);
        $msg = $this->subject->getMessage();

        $this->assertEquals(['foo', 'test'], $msg);
    }

    public function testIsEmpty(): void
    {
        $this->subject->add(null);
        $this->subject->addMessageMerge([]);

        $this->assertTrue($this->subject->isEmpty());
    }
}

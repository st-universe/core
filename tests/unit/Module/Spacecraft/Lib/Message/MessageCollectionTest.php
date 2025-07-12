<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Message;

use Mockery\MockInterface;
use Override;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\StuTestCase;

class MessageCollectionTest extends StuTestCase
{
    private MockInterface&MessageFactoryInterface $messageFactory;

    private MessageCollectionInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->subject = new MessageCollection(
            $this->messageFactory
        );
    }

    public function testGetRecipientIds(): void
    {
        $message1 = $this->mock(MessageInterface::class);
        $message2 = $this->mock(MessageInterface::class);
        $message3 = $this->mock(MessageInterface::class);
        $message4 = $this->mock(MessageInterface::class);

        $message1->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $message1->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);
        $message2->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(5);
        $message2->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);
        $message3->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(UserConstants::USER_NOONE);
        $message3->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);
        $message4->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(true);

        $this->subject->add($message1);
        $this->subject->add($message2);
        $this->subject->add($message3);

        $result = $this->subject->getRecipientIds();

        $this->assertEquals([42, 5], $result);
    }

    public function testGetInformationDumpExpectEverythingWhenParameterIsNull(): void
    {
        $message1 = $this->mock(MessageInterface::class);
        $message2 = $this->mock(MessageInterface::class);

        $message1->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);
        $message1->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message1-a', 'message1-b']);
        $message2->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);
        $message2->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message2-a', 'message2-b']);

        $this->subject->add($message1);
        $this->subject->add($message2);

        $result = $this->subject->getInformationDump();

        $this->assertEquals(['message1-a', 'message1-b', 'message2-a', 'message2-b'], $result->getInformations());
    }

    public function testGetInformationDumpExpectFilteredResultWhenParameterIsNotNull(): void
    {
        $message5to42 = $this->mock(MessageInterface::class);
        $message2 = $this->mock(MessageInterface::class);
        $message5to666 = $this->mock(MessageInterface::class);
        $message42to5 = $this->mock(MessageInterface::class);

        $message5to42->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);
        $message5to42->shouldReceive('getSenderId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $message5to42->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);
        $message5to42->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message5to42-a', 'message5to42-b']);

        $message2->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);
        $message2->shouldReceive('getSenderId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $message2->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(null);
        $message2->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message2-a', 'message2-b']);

        $message5to666->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);
        $message5to666->shouldReceive('getSenderId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $message5to666->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(666);

        $message42to5->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);
        $message42to5->shouldReceive('getSenderId')
            ->withNoArgs()
            ->andReturn(42);
        $message42to5->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(5);
        $message42to5->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message42to5']);

        $this->subject->add($message5to42);
        $this->subject->add($message2);
        $this->subject->add($message5to666);
        $this->subject->add($message42to5);

        $result = $this->subject->getInformationDump(42);

        $this->assertEquals([
            'message5to42-a',
            'message5to42-b',
            'message2-a',
            'message2-b',
            'message42to5'
        ], $result->getInformations());
    }

    public function testIsEmptyExpectFalseWhenNotEmpty(): void
    {
        $this->subject->add(new Message(1, null, ['foo']));

        $result = $this->subject->isEmpty();

        $this->assertFalse($result);
    }

    public function testIsEmptyExpectTrueWhenEmpty(): void
    {
        $this->subject->add(new Message(1, null));

        $result = $this->subject->isEmpty();

        $this->assertTrue($result);
    }
}

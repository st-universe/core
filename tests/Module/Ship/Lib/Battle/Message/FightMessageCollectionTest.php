<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Message;

use Stu\StuTestCase;

class FightMessageCollectionTest extends StuTestCase
{
    private FightMessageCollectionInterface $subject;

    public function setUp(): void
    {
        $this->subject = new FightMessageCollection();
    }

    public function testGetRecipientIds(): void
    {
        $fightMessage1 = $this->mock(FightMessageInterface::class);
        $fightMessage2 = $this->mock(FightMessageInterface::class);

        $fightMessage1->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $fightMessage2->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(5);

        $this->subject->add($fightMessage1);
        $this->subject->add($fightMessage2);
        $this->subject->add($fightMessage2);

        $result = $this->subject->getRecipientIds();

        $this->assertEquals([42, 5], $result);
    }

    public function testGetMessageDumpExpectEverythingWhenParameterIsNull(): void
    {
        $fightMessage1 = $this->mock(FightMessageInterface::class);
        $fightMessage2 = $this->mock(FightMessageInterface::class);

        $fightMessage1->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message1-a', 'message1-b']);
        $fightMessage2->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message2-a', 'message2-b']);

        $this->subject->addMultiple([$fightMessage1, $fightMessage2]);

        $result = $this->subject->getMessageDump();

        $this->assertEquals(['message1-a', 'message1-b', 'message2-a', 'message2-b'], $result);
    }

    public function testGetMessageDumpExpectFilteredResultWhenParameterIsNotNull(): void
    {
        $fightMessage1 = $this->mock(FightMessageInterface::class);
        $fightMessage2 = $this->mock(FightMessageInterface::class);
        $fightMessage3 = $this->mock(FightMessageInterface::class);

        $fightMessage1->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);
        $fightMessage1->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message1-a', 'message1-b']);
        $fightMessage2->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(null);
        $fightMessage2->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message2-a', 'message2-b']);
        $fightMessage3->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(666);

        $this->subject->add($fightMessage1);
        $this->subject->add($fightMessage2);
        $this->subject->add($fightMessage3);

        $result = $this->subject->getMessageDump(42);

        $this->assertEquals(['message1-a', 'message1-b', 'message2-a', 'message2-b'], $result);
    }
}

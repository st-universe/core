<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Message;

use Stu\Module\PlayerSetting\Lib\UserEnum;
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
        $fightMessage3 = $this->mock(FightMessageInterface::class);

        $fightMessage1->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $fightMessage2->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(5);
        $fightMessage3->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(UserEnum::USER_NOONE);

        $this->subject->add($fightMessage1);
        $this->subject->add($fightMessage2);
        $this->subject->add($fightMessage3);

        $result = $this->subject->getRecipientIds();

        $this->assertEquals([42, 5], $result);
    }

    public function testGetInformationDumpExpectEverythingWhenParameterIsNull(): void
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

        $result = $this->subject->getInformationDump();

        $this->assertEquals(['message1-a', 'message1-b', 'message2-a', 'message2-b'], $result->getInformations());
    }

    public function testGetInformationDumpExpectFilteredResultWhenParameterIsNotNull(): void
    {
        $fightMessage5to42 = $this->mock(FightMessageInterface::class);
        $fightMessage2 = $this->mock(FightMessageInterface::class);
        $fightMessage5to666 = $this->mock(FightMessageInterface::class);
        $fightMessage42to5 = $this->mock(FightMessageInterface::class);

        $fightMessage5to42->shouldReceive('getSenderId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $fightMessage5to42->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);
        $fightMessage5to42->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message5to42-a', 'message5to42-b']);

        $fightMessage2->shouldReceive('getSenderId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $fightMessage2->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(null);
        $fightMessage2->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message2-a', 'message2-b']);

        $fightMessage5to666->shouldReceive('getSenderId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $fightMessage5to666->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(666);

        $fightMessage42to5->shouldReceive('getSenderId')
            ->withNoArgs()
            ->andReturn(42);
        $fightMessage42to5->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->andReturn(5);
        $fightMessage42to5->shouldReceive('getMessage')
            ->withNoArgs()
            ->once()
            ->andReturn(['message42to5']);

        $this->subject->add($fightMessage5to42);
        $this->subject->add($fightMessage2);
        $this->subject->add($fightMessage5to666);
        $this->subject->add($fightMessage42to5);

        $result = $this->subject->getInformationDump(42);

        $this->assertEquals([
            'message5to42-a',
            'message5to42-b',
            'message2-a',
            'message2-b',
            'message42to5'
        ], $result->getInformations());
    }
}

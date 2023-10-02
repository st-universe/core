<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Mockery\MockInterface;
use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\Message\MessageCollectionInterface;
use Stu\StuTestCase;

class DistributedMessageSenderTest extends StuTestCase
{
    /**
     * @var MockInterface|PrivateMessageSenderInterface
     */
    private PrivateMessageSenderInterface $privateMessageSender;

    private DistributedMessageSenderInterface $subject;

    public function setUp(): void
    {
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->subject = new DistributedMessageSender(
            $this->privateMessageSender
        );
    }

    public function testDistributeMessageCollection(): void
    {
        $messageCollection = $this->mock(MessageCollectionInterface::class);
        $info1 = $this->mock(InformationWrapper::class);
        $info2 = $this->mock(InformationWrapper::class);

        $messageCollection->shouldReceive('getRecipientIds')
            ->withNoArgs()
            ->once()
            ->andReturn([1, 2]);
        $messageCollection->shouldReceive('getInformationDump')
            ->with(1)
            ->once()
            ->andReturn($info1);
        $messageCollection->shouldReceive('getInformationDump')
            ->with(2)
            ->once()
            ->andReturn($info2);

        $info1->shouldReceive('getInformationsAsString')
            ->withNoArgs()
            ->andReturn("INFO_1");
        $info2->shouldReceive('getInformationsAsString')
            ->withNoArgs()
            ->andReturn("INFO_2");

        $this->privateMessageSender->shouldReceive('send')
            ->with(42, 1, "HEADER\n\nINFO_1", 123)
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(42, 2, "HEADER\n\nINFO_2", 123)
            ->once();

        $this->subject->distributeMessageCollection(
            $messageCollection,
            42,
            123,
            "HEADER"
        );
    }
}

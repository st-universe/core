<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ShipUndockingTest extends StuTestCase
{
    /** @var MockInterface|ShipRepositoryInterface */
    private MockInterface $shipRepository;

    /** @var MockInterface|PrivateMessageSenderInterface */
    private MockInterface $privateMessageSender;

    private ShipUndockingInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->subject = new ShipUndocking(
            $this->shipRepository,
            $this->privateMessageSender
        );
    }

    public function testUndockAllDockedExpectFalseWhenNothingDocked(): void
    {
        $station = $this->mock(ShipInterface::class);

        $station->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->subject->undockAllDocked($station);

        $this->assertFalse($result);
    }

    public function testUndockAllDockedExpectTrueWhenDocked(): void
    {
        $station = $this->mock(ShipInterface::class);
        $ship1 = $this->mock(ShipInterface::class);
        $ship2 = $this->mock(ShipInterface::class);

        $dockedShips = new ArrayCollection([$ship1, $ship2]);

        $station->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->once()
            ->andReturn($dockedShips);
        $station->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);
        $station->shouldReceive('getName')
            ->withNoArgs()
            ->twice()
            ->andReturn('STATION');

        $ship1->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $ship1->shouldReceive('setDockedToId')
            ->with(null)
            ->once();
        $ship1->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $ship1->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP1');
        $ship1->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(66);

        $ship2->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $ship2->shouldReceive('setDockedToId')
            ->with(null)
            ->once();
        $ship2->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(777);
        $ship2->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP2');
        $ship2->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(77);

        $this->shipRepository->shouldReceive('save')
            ->with($ship1)
            ->once();
        $this->shipRepository->shouldReceive('save')
            ->with($ship2)
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                42,
                666,
                'Die SHIP1 wurde von der STATION abgedockt',
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                'ship.php?SHOW_SHIP=1&id=66'
            )
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                42,
                777,
                'Die SHIP2 wurde von der STATION abgedockt',
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                'ship.php?SHOW_SHIP=1&id=77'
            )
            ->once();

        $result = $this->subject->undockAllDocked($station);

        $this->assertTrue($result);
        $this->assertTrue($dockedShips->isEmpty());
    }
}

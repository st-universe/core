<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ShipUndockingTest extends StuTestCase
{
    private MockInterface&ShipRepositoryInterface $shipRepository;
    private MockInterface&CancelRepairInterface $cancelRepair;
    private MockInterface&CancelRetrofitInterface $cancelRetrofit;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;

    private ShipUndockingInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->cancelRetrofit = $this->mock(CancelRetrofitInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->subject = new ShipUndocking(
            $this->shipRepository,
            $this->cancelRepair,
            $this->cancelRetrofit,
            $this->privateMessageSender
        );
    }

    public function testUndockAllDockedExpectFalseWhenNothingDocked(): void
    {
        $station = $this->mock(Station::class);

        $station->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->subject->undockAllDocked($station);

        $this->assertFalse($result);
    }

    public function testUndockAllDockedExpectTrueWhenDocked(): void
    {
        $station = $this->mock(Station::class);
        $ship1 = $this->mock(Ship::class);
        $ship2 = $this->mock(Ship::class);

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
        $ship1->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $ship1->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP1');

        $ship2->shouldReceive('setDockedTo')
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

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($ship1)
            ->once();
        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($ship2)
            ->once();

        $this->cancelRetrofit->shouldReceive('cancelRetrofit')
            ->with($ship1)
            ->once();
        $this->cancelRetrofit->shouldReceive('cancelRetrofit')
            ->with($ship2)
            ->once();

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
                $ship1
            )
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                42,
                777,
                'Die SHIP2 wurde von der STATION abgedockt',
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $ship2
            )
            ->once();

        $result = $this->subject->undockAllDocked($station);

        $this->assertTrue($result);
        $this->assertTrue($dockedShips->isEmpty());
    }
}

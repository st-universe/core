<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;
use Stu\StuTestCase;

class TholianWebUtilTest extends StuTestCase
{
    private MockInterface&TholianWebRepositoryInterface $tholianWebRepository;
    private MockInterface&SpacecraftSystemRepositoryInterface $shipSystemRepository;
    private MockInterface&StuTime $stuTime;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&EntityManagerInterface $entityManager;

    private TholianWebUtilInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->tholianWebRepository = $this->mock(TholianWebRepositoryInterface::class);
        $this->shipSystemRepository = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->stuTime = $this->mock(StuTime::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->subject = new TholianWebUtil(
            $this->tholianWebRepository,
            $this->shipSystemRepository,
            $this->stuTime,
            $this->privateMessageSender,
            $this->entityManager,
            $this->initLoggerUtil()
        );
    }

    public function testisTargetOutsideFinishedTholianWebExpectFalseWhenNoWeb(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->isTargetOutsideFinishedTholianWeb($ship, $target);

        $this->assertFalse($result);
    }

    public function testisTargetOutsideFinishedTholianWebExpectFalseWhenWebUnfinished(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);
        $web = $this->mock(TholianWeb::class);

        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);
        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->isTargetOutsideFinishedTholianWeb($ship, $target);

        $this->assertFalse($result);
    }

    public function testisTargetOutsideFinishedTholianWebExpectFalseWhenTargetInSameFinishedWeb(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);
        $web = $this->mock(TholianWeb::class);

        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);
        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $target->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);

        $result = $this->subject->isTargetOutsideFinishedTholianWeb($ship, $target);

        $this->assertFalse($result);
    }

    public function testisTargetOutsideFinishedTholianWebExpectTrueWhenTargetOutsideFinishedWeb(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);
        $web = $this->mock(TholianWeb::class);

        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);
        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $target->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->isTargetOutsideFinishedTholianWeb($ship, $target);

        $this->assertTrue($result);
    }
}

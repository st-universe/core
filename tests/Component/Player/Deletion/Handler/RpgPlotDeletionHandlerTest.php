<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\RpgPlotMemberInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class RpgPlotDeletionHandlerTest extends MockeryTestCase
{
    /** @var MockInterface&RpgPlotMemberRepositoryInterface */
    private MockInterface $rpgPlotMemberRepository;

    /** @var MockInterface&RpgPlotRepositoryInterface */
    private MockInterface $rpgPlotRepository;

    /** @var MockInterface&UserRepositoryInterface */
    private MockInterface $userRepository;

    private RpgPlotDeletionHandler $handler;

    public function setUp(): void
    {
        $this->rpgPlotMemberRepository = Mockery::mock(RpgPlotMemberRepositoryInterface::class);
        $this->rpgPlotRepository = Mockery::mock(RpgPlotRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);

        $this->handler = new RpgPlotDeletionHandler(
            $this->rpgPlotMemberRepository,
            $this->rpgPlotRepository,
            $this->userRepository
        );
    }

    public function testDeleteSetsAnotherRpgPlotMemberInCharge(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $newUser = Mockery::mock(UserInterface::class);
        $gameFallbackUser = Mockery::mock(UserInterface::class);
        $rpgPlot = Mockery::mock(RpgPlotInterface::class);
        $rpgPlotMemberUser = Mockery::mock(RpgPlotMemberInterface::class);
        $newRpgPlotMemberUser = Mockery::mock(RpgPlotMemberInterface::class);

        $userId = 666;
        $plotId = 42;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $rpgPlot->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($plotId);
        $rpgPlot->shouldReceive('getMembers->count')
            ->withNoArgs()
            ->once()
            ->andReturn(33);
        $rpgPlot->shouldReceive('getMembers->current')
            ->withNoArgs()
            ->once()
            ->andReturn($newRpgPlotMemberUser);
        $rpgPlot->shouldReceive('setUser')
            ->with($newUser)
            ->once();

        $newRpgPlotMemberUser->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($newUser);

        $this->rpgPlotRepository->shouldReceive('getByFoundingUser')
            ->with($userId)
            ->once()
            ->andReturn([$rpgPlot]);
        $this->rpgPlotRepository->shouldReceive('save')
            ->with($rpgPlot)
            ->once();

        $this->rpgPlotMemberRepository->shouldReceive('getByPlotAndUser')
            ->with($plotId, $userId)
            ->once()
            ->andReturn($rpgPlotMemberUser);
        $this->rpgPlotMemberRepository->shouldReceive('delete')
            ->with($rpgPlotMemberUser)
            ->once();

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($gameFallbackUser);

        $this->handler->delete($user);
    }

    public function testDeleteSetsSystemUser(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $gameFallbackUser = Mockery::mock(UserInterface::class);
        $rpgPlot = Mockery::mock(RpgPlotInterface::class);

        $userId = 666;
        $plotId = 42;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $rpgPlot->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($plotId);
        $rpgPlot->shouldReceive('getMembers->count')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $rpgPlot->shouldReceive('setUser')
            ->with($gameFallbackUser)
            ->once();

        $this->rpgPlotRepository->shouldReceive('getByFoundingUser')
            ->with($userId)
            ->once()
            ->andReturn([$rpgPlot]);
        $this->rpgPlotRepository->shouldReceive('save')
            ->with($rpgPlot)
            ->once();

        $this->rpgPlotMemberRepository->shouldReceive('getByPlotAndUser')
            ->with($plotId, $userId)
            ->once()
            ->andReturnNull();

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($gameFallbackUser);

        $this->handler->delete($user);
    }
}

<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
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

    #[Override]
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

        $members = new ArrayCollection([666 => $rpgPlotMemberUser, $newRpgPlotMemberUser]);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $rpgPlot->shouldReceive('getMembers')
            ->withNoArgs()
            ->once()
            ->andReturn($members);
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

        $members = new ArrayCollection([]);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $rpgPlot->shouldReceive('getMembers')
            ->withNoArgs()
            ->once()
            ->andReturn($members);
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

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($gameFallbackUser);

        $this->handler->delete($user);
    }
}

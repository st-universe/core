<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class RpgPlotDeletionHandlerTest extends MockeryTestCase
{
    private MockInterface&RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;
    private MockInterface&RpgPlotRepositoryInterface $rpgPlotRepository;
    private MockInterface&UserRepositoryInterface $userRepository;
    private MockInterface&EntityManagerInterface $entityManager;

    private RpgPlotDeletionHandler $handler;

    #[\Override]
    public function setUp(): void
    {
        $this->rpgPlotMemberRepository = Mockery::mock(RpgPlotMemberRepositoryInterface::class);
        $this->rpgPlotRepository = Mockery::mock(RpgPlotRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);

        $this->handler = new RpgPlotDeletionHandler(
            $this->rpgPlotMemberRepository,
            $this->rpgPlotRepository,
            $this->userRepository,
            $this->entityManager
        );
    }

    public function testDeleteSetsAnotherRpgPlotMemberInCharge(): void
    {
        $user = Mockery::mock(User::class);
        $newUser = Mockery::mock(User::class);
        $gameFallbackUser = Mockery::mock(User::class);
        $rpgPlot = Mockery::mock(RpgPlot::class);
        $rpgPlotMemberUser = Mockery::mock(RpgPlotMember::class);
        $newRpgPlotMemberUser = Mockery::mock(RpgPlotMember::class);

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

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once()
            ->validateOrder();
        $this->entityManager->shouldReceive('detach')
            ->with($newRpgPlotMemberUser)
            ->once()
            ->validateOrder();
        $this->entityManager->shouldReceive('detach')
            ->with($rpgPlot)
            ->once()
            ->validateOrder();

        $this->handler->delete($user);
        $this->assertTrue($members->isEmpty());
    }

    public function testDeleteSetsSystemUser(): void
    {
        $user = Mockery::mock(User::class);
        $gameFallbackUser = Mockery::mock(User::class);
        $rpgPlot = Mockery::mock(RpgPlot::class);

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

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once()
            ->validateOrder();
        $this->entityManager->shouldReceive('detach')
            ->with($rpgPlot)
            ->once()
            ->validateOrder();

        $this->handler->delete($user);
        $this->assertTrue($members->isEmpty());
    }
}

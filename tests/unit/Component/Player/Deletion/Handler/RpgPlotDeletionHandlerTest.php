<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class RpgPlotDeletionHandlerTest extends StuTestCase
{
    private MockInterface&RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;
    private MockInterface&RpgPlotRepositoryInterface $rpgPlotRepository;
    private MockInterface&UserRepositoryInterface $userRepository;
    private MockInterface&EntityManagerInterface $entityManager;

    private RpgPlotDeletionHandler $handler;

    #[\Override]
    public function setUp(): void
    {
        $this->rpgPlotMemberRepository = $this->mock(RpgPlotMemberRepositoryInterface::class);
        $this->rpgPlotRepository = $this->mock(RpgPlotRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->handler = new RpgPlotDeletionHandler(
            $this->rpgPlotMemberRepository,
            $this->rpgPlotRepository,
            $this->userRepository,
            $this->entityManager
        );
    }

    public function testDeleteSetsAnotherRpgPlotMemberInCharge(): void
    {
        $user = $this->mock(User::class);
        $newUser = $this->mock(User::class);
        $gameFallbackUser = $this->mock(User::class);
        $rpgPlot = $this->mock(RpgPlot::class);
        $rpgPlotMemberUser = $this->mock(RpgPlotMember::class);
        $newRpgPlotMemberUser = $this->mock(RpgPlotMember::class);

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
        $user = $this->mock(User::class);
        $gameFallbackUser = $this->mock(User::class);
        $rpgPlot = $this->mock(RpgPlot::class);

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

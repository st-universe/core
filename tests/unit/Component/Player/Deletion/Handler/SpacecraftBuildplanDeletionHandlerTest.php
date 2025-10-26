<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftBuildplanDeletionHandlerTest extends StuTestCase
{
    private MockInterface&SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository;
    private MockInterface&UserRepositoryInterface $userRepository;

    private SpacecraftBuildplanDeletionHandler $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new SpacecraftBuildplanDeletionHandler(
            $this->spacecraftBuildplanRepository,
            $this->userRepository
        );
    }

    public function testDeleteDeletesBuildplans(): void
    {
        $user = $this->mock(User::class);
        $foreignShipUser = $this->mock(User::class);
        $foreignBuildplansUser = $this->mock(User::class);
        $spacecraftBuildplanWithoutShips = $this->mock(SpacecraftBuildplan::class);
        $spacecraftBuildplanWithOwnShip = $this->mock(SpacecraftBuildplan::class);
        $spacecraftBuildplanWithForeignShip = $this->mock(SpacecraftBuildplan::class);
        $ownShip = $this->mock(Ship::class);
        $foreignShip = $this->mock(Ship::class);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($userId);

        $foreignShipUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(777);
        $foreignBuildplansUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(888);

        $spacecraftBuildplanWithoutShips->shouldReceive('getSpacecraftList')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $spacecraftBuildplanWithOwnShip->shouldReceive('getSpacecraftList')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ownShip]));
        $spacecraftBuildplanWithOwnShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $spacecraftBuildplanWithForeignShip->shouldReceive('getSpacecraftList')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$foreignShip]));
        $spacecraftBuildplanWithForeignShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $spacecraftBuildplanWithForeignShip->shouldReceive('setUser')
            ->with($foreignBuildplansUser)
            ->once();

        $ownShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $foreignShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($foreignShipUser);

        $this->spacecraftBuildplanRepository->shouldReceive('getByUser')
            ->with($userId)
            ->once()
            ->andReturn([$spacecraftBuildplanWithoutShips, $spacecraftBuildplanWithOwnShip, $spacecraftBuildplanWithForeignShip]);
        $this->spacecraftBuildplanRepository->shouldReceive('delete')
            ->with($spacecraftBuildplanWithoutShips)
            ->once();
        $this->spacecraftBuildplanRepository->shouldReceive('delete')
            ->with($spacecraftBuildplanWithOwnShip)
            ->once();
        $this->spacecraftBuildplanRepository->shouldReceive('save')
            ->with($spacecraftBuildplanWithForeignShip)
            ->once();

        $this->userRepository->shouldReceive('find')
            ->with(UserConstants::USER_FOREIGN_BUILDPLANS)
            ->once()
            ->andReturn($foreignBuildplansUser);

        $this->subject->delete($user);
    }
}

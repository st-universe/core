<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftBuildplanDeletionHandlerTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftBuildplanRepositoryInterface */
    private $spacecraftBuildplanRepository;
    /** @var MockInterface&UserRepositoryInterface */
    private $userRepository;

    private SpacecraftBuildplanDeletionHandler $subject;

    #[Override]
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
        $user = $this->mock(UserInterface::class);
        $foreignBuildplansUser = $this->mock(UserInterface::class);
        $spacecraftBuildplanWithoutShips = $this->mock(SpacecraftBuildplanInterface::class);
        $spacecraftBuildplanWithOwnShip = $this->mock(SpacecraftBuildplanInterface::class);
        $spacecraftBuildplanWithForeignShip = $this->mock(SpacecraftBuildplanInterface::class);
        $ownShip = $this->mock(ShipInterface::class);
        $foreignShip = $this->mock(ShipInterface::class);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $spacecraftBuildplanWithoutShips->shouldReceive('getShiplist')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $spacecraftBuildplanWithOwnShip->shouldReceive('getShiplist')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ownShip]));
        $spacecraftBuildplanWithOwnShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $spacecraftBuildplanWithForeignShip->shouldReceive('getShiplist')
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
            ->andReturn($this->mock(UserInterface::class));

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
            ->with(UserEnum::USER_FOREIGN_BUILDPLANS)
            ->once()
            ->andReturn($foreignBuildplansUser);

        $this->subject->delete($user);
    }
}

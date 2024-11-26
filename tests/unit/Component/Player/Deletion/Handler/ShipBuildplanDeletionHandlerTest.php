<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class ShipBuildplanDeletionHandlerTest extends StuTestCase
{
    /** @var MockInterface&ShipBuildplanRepositoryInterface */
    private $shipBuildplanRepository;
    /** @var MockInterface&UserRepositoryInterface */
    private $userRepository;

    private ShipBuildplanDeletionHandler $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipBuildplanRepository = $this->mock(ShipBuildplanRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new ShipBuildplanDeletionHandler(
            $this->shipBuildplanRepository,
            $this->userRepository
        );
    }

    public function testDeleteDeletesBuildplans(): void
    {
        $user = $this->mock(UserInterface::class);
        $foreignBuildplansUser = $this->mock(UserInterface::class);
        $shipBuildplanWithoutShips = $this->mock(ShipBuildplanInterface::class);
        $shipBuildplanWithOwnShip = $this->mock(ShipBuildplanInterface::class);
        $shipBuildplanWithForeignShip = $this->mock(ShipBuildplanInterface::class);
        $ownShip = $this->mock(ShipInterface::class);
        $foreignShip = $this->mock(ShipInterface::class);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $shipBuildplanWithoutShips->shouldReceive('getShiplist')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $shipBuildplanWithOwnShip->shouldReceive('getShiplist')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ownShip]));
        $shipBuildplanWithOwnShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $shipBuildplanWithForeignShip->shouldReceive('getShiplist')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$foreignShip]));
        $shipBuildplanWithForeignShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $shipBuildplanWithForeignShip->shouldReceive('setUser')
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

        $this->shipBuildplanRepository->shouldReceive('getByUser')
            ->with($userId)
            ->once()
            ->andReturn([$shipBuildplanWithoutShips, $shipBuildplanWithOwnShip, $shipBuildplanWithForeignShip]);
        $this->shipBuildplanRepository->shouldReceive('delete')
            ->with($shipBuildplanWithoutShips)
            ->once();
        $this->shipBuildplanRepository->shouldReceive('delete')
            ->with($shipBuildplanWithOwnShip)
            ->once();
        $this->shipBuildplanRepository->shouldReceive('save')
            ->with($shipBuildplanWithForeignShip)
            ->once();

        $this->userRepository->shouldReceive('find')
            ->with(UserEnum::USER_FOREIGN_BUILDPLANS)
            ->once()
            ->andReturn($foreignBuildplansUser);

        $this->subject->delete($user);
    }
}

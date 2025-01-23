<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftDeletionHandlerTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftRepositoryInterface */
    private $spacecraftRepository;
    /** @var MockInterface&ConstructionProgressRepositoryInterface */
    private $constructionProgressRepository;
    /** @var MockInterface&SpacecraftRemoverInterface */
    private $spacecraftRemover;
    /** @var MockInterface&SpacecraftSystemManagerInterface */
    private $spacecraftSystemManager;
    /** @var MockInterface&SpacecraftWrapperFactoryInterface */
    private $spacecraftWrapperFactory;
    /** @var MockInterface&ShipUndockingInterface */
    private $shipUndocking;
    /** @var MockInterface&EntityManagerInterface */
    private $entityManager;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
    public function setUp(): void
    {
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->constructionProgressRepository = $this->mock(ConstructionProgressRepositoryInterface::class);
        $this->spacecraftRemover = $this->mock(SpacecraftRemoverInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->shipUndocking = $this->mock(ShipUndockingInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->handler = new SpacecraftDeletionHandler(
            $this->spacecraftRepository,
            $this->constructionProgressRepository,
            $this->spacecraftRemover,
            $this->spacecraftSystemManager,
            $this->spacecraftWrapperFactory,
            $this->shipUndocking,
            $this->entityManager
        );
    }

    public function testDeleteDeletesUserShips(): void
    {
        $user = $this->mock(UserInterface::class);
        $station = $this->mock(StationInterface::class);
        $stationWithProgress = $this->mock(StationInterface::class);
        $constructionProgress = $this->mock(ConstructionProgressInterface::class);
        $tradepostStation = $this->mock(StationInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $tractoredShip = $this->mock(ShipInterface::class);

        $station->shouldReceive('getTradePost')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $stationWithProgress->shouldReceive('getTradePost')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $tradepostStation->shouldReceive('getTradePost')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(TradePostInterface::class));

        $station->shouldReceive('getConstructionProgress')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $stationWithProgress->shouldReceive('getConstructionProgress')
            ->withNoArgs()
            ->once()
            ->andReturn($constructionProgress);

        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShip);
        $station->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $stationWithProgress->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($ship)
            ->once()
            ->andReturn($wrapper);

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with(
                $wrapper,
                SpacecraftSystemTypeEnum::TRACTOR_BEAM,
                true
            )
            ->once();

        $this->spacecraftRepository->shouldReceive('getByUser')
            ->with($user)
            ->once()
            ->andReturn([$ship, $tradepostStation, $station, $stationWithProgress]);

        $this->spacecraftRemover->shouldReceive('remove')
            ->with($ship, true)
            ->once();
        $this->spacecraftRemover->shouldReceive('remove')
            ->with($station, true)
            ->once();
        $this->spacecraftRemover->shouldReceive('remove')
            ->with($stationWithProgress, true)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($station)
            ->once()
            ->andReturn(true);
        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($stationWithProgress)
            ->once()
            ->andReturn(false);

        $this->constructionProgressRepository->shouldReceive('delete')
            ->with($constructionProgress)
            ->once();

        $this->handler->delete($user);
    }
}

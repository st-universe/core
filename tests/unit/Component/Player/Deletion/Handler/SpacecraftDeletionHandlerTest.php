<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\MiningQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftDeletionHandlerTest extends StuTestCase
{
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&ConstructionProgressRepositoryInterface $constructionProgressRepository;
    private MockInterface&SpacecraftRemoverInterface $spacecraftRemover;
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&ShipUndockingInterface $shipUndocking;
    private MockInterface&EntityManagerInterface $entityManager;
    private MockInterface&MiningQueueRepositoryInterface $miningQueueRepository;

    private PlayerDeletionHandlerInterface $handler;

    #[\Override]
    public function setUp(): void
    {
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->constructionProgressRepository = $this->mock(ConstructionProgressRepositoryInterface::class);
        $this->spacecraftRemover = $this->mock(SpacecraftRemoverInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->shipUndocking = $this->mock(ShipUndockingInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->miningQueueRepository = $this->mock(MiningQueueRepositoryInterface::class);

        $this->handler = new SpacecraftDeletionHandler(
            $this->spacecraftRepository,
            $this->constructionProgressRepository,
            $this->spacecraftRemover,
            $this->spacecraftSystemManager,
            $this->spacecraftWrapperFactory,
            $this->shipUndocking,
            $this->entityManager,
            $this->miningQueueRepository
        );
    }

    public function testDeleteDeletesUserShips(): void
    {
        $user = $this->mock(User::class);
        $station = $this->mock(Station::class);
        $stationWithProgress = $this->mock(Station::class);
        $constructionProgress = $this->mock(ConstructionProgress::class);
        $tradepostStation = $this->mock(Station::class);
        $ship = $this->mock(Ship::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $tractoredShip = $this->mock(Ship::class);

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
            ->andReturn($this->mock(TradePost::class));

        $station->shouldReceive('getConstructionProgress')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $stationWithProgress->shouldReceive('getConstructionProgress')
            ->withNoArgs()
            ->once()
            ->andReturn($constructionProgress);
        $stationWithProgress->shouldReceive('resetConstructionProgress')
            ->withNoArgs()
            ->once();

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

        $ship->shouldReceive('getMiningQueue')
            ->withNoArgs()
            ->once()
            ->andReturn(null);


        $this->handler->delete($user);
    }
}

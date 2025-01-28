<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\Module\ModuleRecyclingInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Station\Lib\Creation\StationCreatorInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\StuTestCase;

class StationUtilityTest extends StuTestCase
{
    /** @var SpacecraftBuildplanRepositoryInterface&MockInterface */
    private $spacecraftBuildplanRepository;
    /** @var ConstructionProgressRepositoryInterface&MockInterface */
    private $constructionProgressRepository;
    /** @var ConstructionProgressModuleRepositoryInterface&MockInterface */
    private $constructionProgressModuleRepository;
    /** @var StationCreatorInterface&MockInterface */
    private $stationCreator;
    /** @var StationRepositoryInterface&MockInterface */
    private $stationRepository;
    /** @var StorageManagerInterface&MockInterface */
    private $storageManager;
    /** @var SpacecraftRumpRepositoryInterface&MockInterface */
    private $spacecraftRumpRepository;
    /** @var TradePostRepositoryInterface&MockInterface */
    private $tradePostRepository;
    /** @var TradeLicenseRepositoryInterface&MockInterface */
    private $tradeLicenseRepository;
    /** @var ModuleRecyclingInterface&MockInterface */
    private $moduleRecycling;

    private StationUtilityInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->constructionProgressRepository = $this->mock(ConstructionProgressRepositoryInterface::class);
        $this->constructionProgressModuleRepository = $this->mock(ConstructionProgressModuleRepositoryInterface::class);
        $this->stationCreator = $this->mock(StationCreatorInterface::class);
        $this->stationRepository = $this->mock(StationRepositoryInterface::class);
        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->spacecraftRumpRepository = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $this->tradePostRepository = $this->mock(TradePostRepositoryInterface::class);
        $this->tradeLicenseRepository = $this->mock(TradeLicenseRepositoryInterface::class);
        $this->moduleRecycling = $this->mock(ModuleRecyclingInterface::class);

        $this->subject = new StationUtility(
            $this->spacecraftBuildplanRepository,
            $this->constructionProgressRepository,
            $this->constructionProgressModuleRepository,
            $this->stationCreator,
            $this->stationRepository,
            $this->storageManager,
            $this->spacecraftRumpRepository,
            $this->tradePostRepository,
            $this->tradeLicenseRepository,
            $this->moduleRecycling,
            $this->initLoggerUtil()
        );
    }

    public function testGetDockedWorkbeeCount(): void
    {
        $station = $this->mock(StationInterface::class);
        $docked1 = $this->mock(ShipInterface::class);
        $docked2 = $this->mock(ShipInterface::class);
        $docked3 = $this->mock(ShipInterface::class);
        $docked4 = $this->mock(ShipInterface::class);

        $station->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$docked1, $docked2, $docked3, $docked4]));

        $docked1->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $docked2->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $docked2->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $docked3->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $docked3->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $docked3->shouldReceive('getRump->isWorkbee')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $docked4->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $docked4->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $docked4->shouldReceive('getRump->isWorkbee')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->getDockedWorkbeeCount($station);

        $this->assertEquals(1, $result);
    }
}

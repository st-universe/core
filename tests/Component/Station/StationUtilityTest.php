<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\StuTestCase;

class StationUtilityTest extends StuTestCase
{
    /** @var ShipBuildplanRepositoryInterface|MockInterface */
    private $shipBuildplanRepository;
    /** @var ConstructionProgressRepositoryInterface|MockInterface */
    private $constructionProgressRepository;
    /** @var ConstructionProgressModuleRepositoryInterface|MockInterface */
    private $constructionProgressModuleRepository;
    /** @var ShipCreatorInterface|MockInterface */
    private $shipCreator;
    /** @var ShipRepositoryInterface|MockInterface */
    private $shipRepository;
    /** @var ShipStorageManagerInterface|MockInterface */
    private $shipStorageManager;
    /** @var ShipRumpRepositoryInterface|MockInterface */
    private $shipRumpRepository;
    /** @var TradePostRepositoryInterface|MockInterface */
    private $tradePostRepository;
    /** @var TradeLicenseRepositoryInterface|MockInterface */
    private $tradeLicenseRepository;

    private StationUtilityInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->shipBuildplanRepository = $this->mock(ShipBuildplanRepositoryInterface::class);
        $this->constructionProgressRepository = $this->mock(ConstructionProgressRepositoryInterface::class);
        $this->constructionProgressModuleRepository = $this->mock(ConstructionProgressModuleRepositoryInterface::class);
        $this->shipCreator = $this->mock(ShipCreatorInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->shipStorageManager = $this->mock(ShipStorageManagerInterface::class);
        $this->shipRumpRepository = $this->mock(ShipRumpRepositoryInterface::class);
        $this->tradePostRepository = $this->mock(TradePostRepositoryInterface::class);
        $this->tradeLicenseRepository = $this->mock(TradeLicenseRepositoryInterface::class);

        $this->subject = new StationUtility(
            $this->shipBuildplanRepository,
            $this->constructionProgressRepository,
            $this->constructionProgressModuleRepository,
            $this->shipCreator,
            $this->shipRepository,
            $this->shipStorageManager,
            $this->shipRumpRepository,
            $this->tradePostRepository,
            $this->tradeLicenseRepository,
            $this->initLoggerUtil()
        );
    }

    public function testGetDockedWorkbeeCount(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $docked1 = $this->mock(ShipInterface::class);
        $docked2 = $this->mock(ShipInterface::class);
        $docked3 = $this->mock(ShipInterface::class);
        $docked4 = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getDockedShips')
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

        $result = $this->subject->getDockedWorkbeeCount($ship);

        $this->assertEquals(1, $result);
    }
}

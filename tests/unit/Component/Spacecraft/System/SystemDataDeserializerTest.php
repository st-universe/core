<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

use Doctrine\Common\Collections\ArrayCollection;
use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\Data\HullSystemData;
use Stu\Component\Spacecraft\System\Data\ShipSystemDataFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

class SystemDataDeserializerTest extends StuTestCase
{
    private MockInterface&ShipSystemDataFactoryInterface $shipSystemDataFactory;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private JsonMapperInterface $jsonMapper;

    private MockInterface&Ship $ship;
    private MockInterface&SpacecraftSystem $shipSystem;

    private SystemDataDeserializerInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->shipSystemDataFactory = $this->mock(ShipSystemDataFactoryInterface::class);
        $this->jsonMapper = (new JsonMapperFactory())->bestFit();

        $this->ship = $this->mock(Ship::class);
        $this->shipSystem = $this->mock(SpacecraftSystem::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);

        $this->subject = new SystemDataDeserializer(
            $this->shipSystemDataFactory,
            $this->jsonMapper
        );
    }

    public function testGetHullSystemData(): void
    {
        $hullSystemData = $this->mock(HullSystemData::class);

        $hullSystemData->shouldReceive('setSpacecraft')
            ->with($this->ship)
            ->once();

        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(SpacecraftSystemTypeEnum::HULL, $this->spacecraftWrapperFactory)
            ->once()
            ->andReturn($hullSystemData);

        $hull = $this->subject->getSpecificShipSystem(
            $this->ship,
            SpacecraftSystemTypeEnum::HULL,
            HullSystemData::class,
            new ArrayCollection(),
            $this->spacecraftWrapperFactory
        );

        $this->assertEquals($hullSystemData, $hull);
    }

    public function testGetEpsSystemDataReturnNullIfSystemNotFound(): void
    {
        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::EPS)
            ->once()
            ->andReturn(false);

        $eps = $this->subject->getSpecificShipSystem(
            $this->ship,
            SpacecraftSystemTypeEnum::EPS,
            EpsSystemData::class,
            new ArrayCollection(),
            $this->spacecraftWrapperFactory
        );

        $this->assertNull($eps);
    }

    public function testGetEpsSystemDataWithDataEmptyExpectDefaultValues(): void
    {
        $shipSystemRepo = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $epsSystemData = new EpsSystemData(
            $shipSystemRepo,
            $this->mock(StatusBarFactoryInterface::class)
        );

        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::EPS)
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::EPS)
            ->once()
            ->andReturn($this->shipSystem);
        $this->shipSystem->shouldReceive('getData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(SpacecraftSystemTypeEnum::EPS, $this->spacecraftWrapperFactory)
            ->once()
            ->andReturn($epsSystemData);

        $eps = $this->subject->getSpecificShipSystem(
            $this->ship,
            SpacecraftSystemTypeEnum::EPS,
            EpsSystemData::class,
            new ArrayCollection(),
            $this->spacecraftWrapperFactory
        );

        $this->assertEquals(0, $eps->getEps());
        $this->assertEquals(0, $eps->getTheoreticalMaxEps());
        $this->assertEquals(0, $eps->getBattery());
        $this->assertEquals(0, $eps->getMaxBattery());
        $this->assertEquals(0, $eps->getBatteryCooldown());
        $this->assertEquals(false, $eps->reloadBattery());
    }

    public function testGetEpsSystemDataWithDataNotEmptyExpectCorrectValues(): void
    {
        $shipSystemRepo = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $epsSystemData = new EpsSystemData(
            $shipSystemRepo,
            $this->mock(StatusBarFactoryInterface::class)
        );

        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::EPS)
            ->twice()
            ->andReturn(true);
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::EPS)
            ->once()
            ->andReturn($this->shipSystem);
        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(SpacecraftSystemTypeEnum::EPS, $this->spacecraftWrapperFactory)
            ->once()
            ->andReturn($epsSystemData);
        $this->shipSystem->shouldReceive('getData')
            ->withNoArgs()
            ->once()
            ->andReturn('{
                "eps": 13,
                "maxEps": 27,
                "battery": 1,
                "maxBattery": 55,
                "batteryCooldown": 42,
                "reloadBattery": true }
            ');

        $cache = new ArrayCollection();

        // call two times to check if cache works
        $eps = $this->subject->getSpecificShipSystem(
            $this->ship,
            SpacecraftSystemTypeEnum::EPS,
            EpsSystemData::class,
            $cache,
            $this->spacecraftWrapperFactory
        );
        $eps = $this->subject->getSpecificShipSystem(
            $this->ship,
            SpacecraftSystemTypeEnum::EPS,
            EpsSystemData::class,
            $cache,
            $this->spacecraftWrapperFactory
        );

        $this->assertEquals($epsSystemData, $eps);
        $this->assertEquals(13, $eps->getEps());
        $this->assertEquals(27, $eps->getTheoreticalMaxEps());
        $this->assertEquals(1, $eps->getBattery());
        $this->assertEquals(55, $eps->getMaxBattery());
        $this->assertEquals(42, $eps->getBatteryCooldown());
        $this->assertEquals(true, $eps->reloadBattery());
    }
}

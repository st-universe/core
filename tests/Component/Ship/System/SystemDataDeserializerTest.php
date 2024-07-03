<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use JsonMapper\JsonMapperFactory;
use Mockery\MockInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\StuTestCase;

class SystemDataDeserializerTest extends StuTestCase
{
    /** @var MockInterface|ShipSystemDataFactoryInterface */
    private $shipSystemDataFactory;
    /** @var MockInterface|ShipWrapperFactoryInterface */
    private $shipWrapperFactory;
    /** @var MockInterface|JsonMapperInterface */
    private $jsonMapper;

    /** @var MockInterface|ShipInterface */
    private $ship;
    /** @var MockInterface|ShipSystemInterface */
    private $shipSystem;

    private SystemDataDeserializerInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->shipSystemDataFactory = $this->mock(ShipSystemDataFactoryInterface::class);
        $this->jsonMapper = (new JsonMapperFactory())->bestFit();

        $this->ship = $this->mock(ShipInterface::class);
        $this->shipSystem = $this->mock(ShipSystemInterface::class);
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);

        $this->subject = new SystemDataDeserializer(
            $this->shipSystemDataFactory,
            $this->jsonMapper
        );
    }

    public function testGetHullSystemData(): void
    {
        $hullSystemData = $this->mock(HullSystemData::class);

        $hullSystemData->shouldReceive('setShip')
            ->with($this->ship)
            ->once();

        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(ShipSystemTypeEnum::SYSTEM_HULL, $this->shipWrapperFactory)
            ->once()
            ->andReturn($hullSystemData);

        $hull = $this->subject->getSpecificShipSystem(
            $this->ship,
            ShipSystemTypeEnum::SYSTEM_HULL,
            HullSystemData::class,
            new ArrayCollection(),
            $this->shipWrapperFactory
        );

        $this->assertEquals($hullSystemData, $hull);
    }

    public function testGetEpsSystemDataReturnNullIfSystemNotFound(): void
    {
        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->once()
            ->andReturn(false);

        $eps = $this->subject->getSpecificShipSystem(
            $this->ship,
            ShipSystemTypeEnum::SYSTEM_EPS,
            EpsSystemData::class,
            new ArrayCollection(),
            $this->shipWrapperFactory
        );

        $this->assertNull($eps);
    }

    public function testGetEpsSystemDataWithDataEmptyExpectDefaultValues(): void
    {
        $shipSystemRepo = $this->mock(ShipSystemRepositoryInterface::class);
        $epsSystemData = new EpsSystemData($shipSystemRepo);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->once()
            ->andReturn($this->shipSystem);
        $this->shipSystem->shouldReceive('getData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS, $this->shipWrapperFactory)
            ->once()
            ->andReturn($epsSystemData);

        $eps = $this->subject->getSpecificShipSystem(
            $this->ship,
            ShipSystemTypeEnum::SYSTEM_EPS,
            EpsSystemData::class,
            new ArrayCollection(),
            $this->shipWrapperFactory
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
        $shipSystemRepo = $this->mock(ShipSystemRepositoryInterface::class);
        $epsSystemData = new EpsSystemData($shipSystemRepo);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->twice()
            ->andReturn(true);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->once()
            ->andReturn($this->shipSystem);
        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS, $this->shipWrapperFactory)
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
            ShipSystemTypeEnum::SYSTEM_EPS,
            EpsSystemData::class,
            $cache,
            $this->shipWrapperFactory
        );
        $eps = $this->subject->getSpecificShipSystem(
            $this->ship,
            ShipSystemTypeEnum::SYSTEM_EPS,
            EpsSystemData::class,
            $cache,
            $this->shipWrapperFactory
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

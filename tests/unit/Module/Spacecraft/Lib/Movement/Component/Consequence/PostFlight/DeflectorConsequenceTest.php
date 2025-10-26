<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Damage\ApplyFieldDamageInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class DeflectorConsequenceTest extends StuTestCase
{
    private MockInterface&ApplyFieldDamageInterface $applyFieldDamage;

    private FlightConsequenceInterface $subject;

    private MockInterface&Ship $ship;

    private MockInterface&ShipWrapperInterface $wrapper;

    private MockInterface&FlightRouteInterface $flightRoute;

    #[\Override]
    protected function setUp(): void
    {
        $this->applyFieldDamage = $this->mock(ApplyFieldDamageInterface::class);

        $this->ship = $this->mock(Ship::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new DeflectorConsequence(
            $this->applyFieldDamage
        );
    }

    public function testTriggerExpectNothingWhenShipDestroyed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenTractored(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenNoDeflectorCost(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $fieldType = $this->mock(MapFieldType::class);
        $waypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($waypoint);
        $waypoint->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('getSpecialDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $fieldType->shouldReceive('getEnergyCosts')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectSpecialDamageAndDestruction(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $fieldType = $this->mock(MapFieldType::class);
        $waypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->twice()
            ->andReturn(false, true);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn(22);
        $this->ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn(33);

        $this->flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($waypoint);
        $waypoint->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('getSpecialDamage')
            ->withNoArgs()
            ->twice()
            ->andReturn(1);
        $fieldType->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('FELDTYP');

        $this->applyFieldDamage->shouldReceive('damage')
            ->with(
                $this->wrapper,
                1,
                true,
                'FELDTYP in Sektor 22|33',
                $messages
            )
            ->once()
            ->andReturn($fieldType);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectSpecialDamageAndNoDestruction(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $fieldType = $this->mock(MapFieldType::class);
        $waypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->twice()
            ->andReturn(false, false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn(22);
        $this->ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn(33);

        $this->flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($waypoint);
        $waypoint->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('getSpecialDamage')
            ->withNoArgs()
            ->twice()
            ->andReturn(1);
        $fieldType->shouldReceive('getEnergyCosts')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $fieldType->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('FELDTYP');

        $this->applyFieldDamage->shouldReceive('damage')
            ->with(
                $this->wrapper,
                1,
                true,
                'FELDTYP in Sektor 22|33',
                $messages
            )
            ->once()
            ->andReturn($fieldType);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectDamageWhenDeflectorDestroyed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $fieldType = $this->mock(MapFieldType::class);
        $waypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::DEFLECTOR)
            ->once()
            ->andReturn(false);

        $this->flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($waypoint);
        $waypoint->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('getSpecialDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $fieldType->shouldReceive('getEnergyCosts')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $fieldType->shouldReceive('getDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(10);

        $this->applyFieldDamage->shouldReceive('damage')
            ->with(
                $this->wrapper,
                10,
                false,
                'Deflektor außer Funktion.',
                $messages
            )
            ->once()
            ->andReturn($fieldType);


        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectDamageWhenNoEpsInstalled(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $fieldType = $this->mock(MapFieldType::class);
        $waypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::DEFLECTOR)
            ->once()
            ->andReturn(true);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);


        $this->flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($waypoint);
        $waypoint->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('getSpecialDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $fieldType->shouldReceive('getEnergyCosts')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $fieldType->shouldReceive('getDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(10);

        $this->applyFieldDamage->shouldReceive('damage')
            ->with(
                $this->wrapper,
                10,
                false,
                'Nicht genug Energie für den Deflektor.',
                $messages
            )
            ->once()
            ->andReturn($fieldType);


        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectDamageWhenNotEnoughEnergyForDeflector(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $fieldType = $this->mock(MapFieldType::class);
        $epsSystem = $this->mock(EpsSystemData::class);
        $waypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::DEFLECTOR)
            ->once()
            ->andReturn(true);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);

        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $epsSystem->shouldReceive('setEps')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();


        $this->flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($waypoint);
        $waypoint->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('getSpecialDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $fieldType->shouldReceive('getEnergyCosts')
            ->withNoArgs()
            ->twice()
            ->andReturn(1);
        $fieldType->shouldReceive('getDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(10);

        $this->applyFieldDamage->shouldReceive('damage')
            ->with(
                $this->wrapper,
                10,
                false,
                'Nicht genug Energie für den Deflektor.',
                $messages
            )
            ->once()
            ->andReturn($fieldType);


        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNoDamageWhenEnoughEnergyForDeflector(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $fieldType = $this->mock(MapFieldType::class);
        $epsSystem = $this->mock(EpsSystemData::class);
        $waypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::DEFLECTOR)
            ->once()
            ->andReturn(true);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);

        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(3);
        $epsSystem->shouldReceive('lowerEps')
            ->with(1)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();


        $this->flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($waypoint);
        $waypoint->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('getSpecialDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $fieldType->shouldReceive('getEnergyCosts')
            ->withNoArgs()
            ->andReturn(1);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}

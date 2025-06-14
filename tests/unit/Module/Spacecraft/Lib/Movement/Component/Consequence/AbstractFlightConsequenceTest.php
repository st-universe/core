<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence;

use Closure;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class AbstractFlightConsequenceTest extends StuTestCase
{
    private AbstractFlightConsequence $subject;

    public static function providetestTriggerForShipWrapperData(): array
    {
        return [
            [false, false, false, true],
            [false, false, true, true],
            [false, true, false, false],
            [false, true, true, false],
            [true, false, false, true],
            [true, false, true, false],
            [true, true, false, false],
            [true, true, true, false]
        ];
    }

    #[DataProvider('providetestTriggerForShipWrapperData')]
    public function testTriggerForShipWrapper(
        bool $skipWhenTractored,
        bool $isDestroyed,
        bool $isTractored,
        bool $expectExecution
    ): void {

        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $triggered = false;

        $func = function () use (&$triggered): void {
            $triggered = true;
        };

        $this->subject = $this->subject = new class(
            $skipWhenTractored,
            $func,
            $wrapper,
            $flightRoute,
            $messages
        ) extends AbstractFlightConsequence {

            public function __construct(
                private $skipWhenTractored,
                private Closure $func,
                private SpacecraftWrapperInterface $wrapper,
                private FlightRouteInterface $flightRoute,
                private MessageCollectionInterface $messages,
            ) {}

            #[Override]
            protected function skipWhenTractored(): bool
            {
                return $this->skipWhenTractored;
            }

            #[Override]
            protected  function triggerSpecific(
                SpacecraftWrapperInterface $wrapper,
                FlightRouteInterface $flightRoute,
                MessageCollectionInterface $messages
            ): void {
                if (
                    $wrapper === $this->wrapper
                    && $flightRoute === $this->flightRoute
                    && $messages === $this->messages
                ) {
                    $func = $this->func;
                    $func();
                }
            }
        };

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($ship);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn($isDestroyed);
        $ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($isTractored);

        $this->subject->trigger(
            $wrapper,
            $flightRoute,
            $messages
        );

        $this->assertEquals($expectExecution, $triggered);
    }

    public static function providetestTriggerForSpacecraftWrapperData(): array
    {
        return [
            [false, false, false, true],
            [false, true, false, false],
            [true, false, false, true],
            [true, true, false, false],
        ];
    }

    #[DataProvider('providetestTriggerForSpacecraftWrapperData')]
    public function testTriggerForStationWrapper(
        bool $skipWhenTractored,
        bool $isDestroyed,
        bool $isTractored,
        bool $expectExecution
    ): void {

        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $triggered = false;

        $func = function () use (&$triggered): void {
            $triggered = true;
        };

        $this->subject = $this->subject = new class(
            $skipWhenTractored,
            $func,
            $wrapper,
            $flightRoute,
            $messages
        ) extends AbstractFlightConsequence {

            public function __construct(
                private $skipWhenTractored,
                private Closure $func,
                private SpacecraftWrapperInterface $wrapper,
                private FlightRouteInterface $flightRoute,
                private MessageCollectionInterface $messages,
            ) {}

            #[Override]
            protected function skipWhenTractored(): bool
            {
                return $this->skipWhenTractored;
            }

            #[Override]
            protected  function triggerSpecific(
                SpacecraftWrapperInterface $wrapper,
                FlightRouteInterface $flightRoute,
                MessageCollectionInterface $messages
            ): void {
                if (
                    $wrapper === $this->wrapper
                    && $flightRoute === $this->flightRoute
                    && $messages === $this->messages
                ) {
                    $func = $this->func;
                    $func();
                }
            }
        };

        $wrapper->shouldReceive('get->getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn($isDestroyed);

        $this->subject->trigger(
            $wrapper,
            $flightRoute,
            $messages
        );

        $this->assertEquals($expectExecution, $triggered);
    }
}

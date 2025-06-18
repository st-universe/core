<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\StuTestCase;

class FlightCompanyTest extends StuTestCase
{
    /** @var MockInterface&PreFlightConditionsCheckInterface */
    private $preFlightConditionsCheck;

    #[Override]
    protected function setUp(): void
    {
        $this->preFlightConditionsCheck = $this->mock(PreFlightConditionsCheckInterface::class);
    }

    public function testGetLeader(): void
    {
        $spacecraft = $this->mock(SpacecraftInterface::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraft);

        $result = $classToTest->getLeader();

        $this->assertEquals($spacecraft, $result);
    }

    public function testGetLeadWrapper(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $fleetWrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $fleetWrapper->shouldReceive('getLeadWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($wrapper);

        $result = $classToTest->getLeadWrapper();

        $this->assertEquals($wrapper, $result);
    }

    public function testGetActiveMembers(): void
    {
        $spacecraft = $this->mock(SpacecraftInterface::class);
        $destroyedSpacecraft = $this->mock(SpacecraftInterface::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $destroyedWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $members = new ArrayCollection([1 => $wrapper, 2 => $destroyedWrapper]);

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraft);
        $destroyedWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($destroyedSpacecraft);

        $spacecraft->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $destroyedSpacecraft->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $classToTest->getActiveMembers()->toArray();

        $this->assertEquals([1 => $wrapper], $result);
    }

    public function testIsEmpty(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $result = $classToTest->isEmpty();

        $this->assertTrue($result);
    }

    public function testIsEverybodyDestroyedExpectTrueWhenEverybodyDestroyed(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $members = new ArrayCollection([$wrapper]);

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $wrapper->shouldReceive('get->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $classToTest->isEverybodyDestroyed();

        $this->assertTrue($result);
    }

    public function testIsEverybodyDestroyedExpectFalseWhenNotEverybodyDestroyed(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper2 = $this->mock(SpacecraftWrapperInterface::class);
        $members = new ArrayCollection([$wrapper, $wrapper2]);

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $wrapper->shouldReceive('get->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $wrapper2->shouldReceive('get->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $classToTest->isEverybodyDestroyed();

        $this->assertFalse($result);
    }

    public function testIsFleetModeExpectFalseWhenSubjectIsSpacecraft(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $result = $classToTest->isFleetMode();

        $this->assertFalse($result);
    }

    public function testIsFleetModeExpectTrueWhenSubjectIsFleet(): void
    {
        $wrapper = $this->mock(FleetWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $result = $classToTest->isFleetMode();

        $this->assertTrue($result);
    }

    public function testIsFixedFleetModeExpectFalseWhenNotFleet(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $result = $classToTest->isFixedFleetMode();

        $this->assertFalse($result);
    }

    public function testIsFixedFleetModeExpectFalseWhenNotFixedFleet(): void
    {
        $wrapper = $this->mock(FleetWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $wrapper->shouldReceive('get->isFleetFixed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $classToTest->isFixedFleetMode();

        $this->assertFalse($result);
    }

    public function testIsFixedFleetModeExpectTrueWhenFixedFleet(): void
    {
        $wrapper = $this->mock(FleetWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $wrapper->shouldReceive('get->isFleetFixed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $classToTest->isFixedFleetMode();

        $this->assertTrue($result);
    }

    public function testHasToLeaveFleetExpectFalseWhenLeaderIsFleet(): void
    {
        $wrapper = $this->mock(FleetWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $result = $classToTest->hasToLeaveFleet();

        $this->assertFalse($result);
    }

    public function testHasToLeaveFleetExpectFalseWhenLeaderNotInFleet(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $classToTest->hasToLeaveFleet();

        $this->assertFalse($result);
    }

    public function testHasToLeaveFleetExpectTrueWhenSubjectInFleet(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $members = new ArrayCollection();

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(FleetWrapperInterface::class));

        $result = $classToTest->hasToLeaveFleet();

        $this->assertTrue($result);
    }

    public static function getIsFlightPossibleDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    #[DataProvider('getIsFlightPossibleDataProvider')]
    public function testIsFlightPossible(bool $isFlightPossible): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $spacecraft = $this->mock(SpacecraftInterface::class);
        $blockedWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $conditionCheckResult = $this->mock(ConditionCheckResult::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $members = new ArrayCollection([1 => $wrapper, 42 => $blockedWrapper]);

        $classToTest = new FlightCompany(
            $wrapper,
            $members,
            $this->preFlightConditionsCheck
        );

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraft);
        $spacecraft->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->preFlightConditionsCheck->shouldReceive('checkPreconditions')
            ->with($classToTest, $flightRoute, $messages)
            ->once()
            ->andReturn($conditionCheckResult);

        $conditionCheckResult->shouldReceive('getBlockedIds')
            ->withNoArgs()
            ->once()
            ->andReturn([42]);
        $conditionCheckResult->shouldReceive('isFlightPossible')
            ->withNoArgs()
            ->once()
            ->andReturn($isFlightPossible);

        $result = $classToTest->isFlightPossible($flightRoute, $messages);

        $this->assertEquals($isFlightPossible, $result);
        $this->assertEquals([1 => $wrapper], $classToTest->getActiveMembers()->toArray());
    }
}

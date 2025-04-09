<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Config\Init;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition\PreFlightConditionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\StuTestCase;

use function DI\get;

/**
 * Avoid global settings to cause trouble within other tests
 */
class PreFlightConditionsCheckTest extends StuTestCase
{
    public static function provideData(): array
    {
        return [
            [true],
            [false]
        ];
    }

    #[DataProvider('provideData')]
    public function testCheckPreconditions(bool $isFlightPossible): void
    {
        $flightCompany = $this->mock(FlightCompany::class);
        $conditionCheckResultFactory = $this->mock(ConditionCheckResultFactoryInterface::class);
        $wrapper1 = $this->mock(ShipWrapperInterface::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $condition1 = $this->mock(PreFlightConditionInterface::class);
        $condition2 = $this->mock(PreFlightConditionInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $conditionCheckResult = $this->mock(ConditionCheckResult::class);

        $subject = new PreFlightConditionsCheck($conditionCheckResultFactory, [$condition1, $condition2]);

        $conditionCheckResultFactory->shouldReceive('create')
            ->with($flightCompany)
            ->once()
            ->andReturn($conditionCheckResult);

        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$wrapper1, $wrapper2]));

        $condition1->shouldReceive('check')
            ->with($wrapper1, $flightRoute, $conditionCheckResult)
            ->once()
            ->andReturn(false);
        $condition1->shouldReceive('check')
            ->with($wrapper2, $flightRoute, $conditionCheckResult)
            ->once()
            ->andReturn(false);
        $condition2->shouldReceive('check')
            ->with($wrapper1, $flightRoute, $conditionCheckResult)
            ->once()
            ->andReturn(false);
        $condition2->shouldReceive('check')
            ->with($wrapper2, $flightRoute, $conditionCheckResult)
            ->once()
            ->andReturn(false);

        $conditionCheckResult->shouldReceive('isFlightPossible')
            ->withNoArgs()
            ->once()
            ->andReturn($isFlightPossible);
        $conditionCheckResult->shouldReceive('getInformations')
            ->withNoArgs()
            ->once()
            ->andReturn(['INFOS']);

        if (!$isFlightPossible) {
            $messages->shouldReceive('addInformation')
                ->with('Der Weiterflug wurde aus folgenden Gründen abgebrochen:')
                ->once()
                ->ordered();
        }
        $messages->shouldReceive('addMessageBy')
            ->with(['INFOS'])
            ->once()
            ->ordered();

        $result = $subject->checkPreconditions($flightCompany, $flightRoute, $messages);

        $this->assertEquals($conditionCheckResult, $result);
    }

    public function testAllConditionsRegistered(): void
    {
        $dic = Init::getContainer();

        $this->assertEquals(5, count(get(PreFlightConditionInterface::class)->resolve($dic)));
    }
}

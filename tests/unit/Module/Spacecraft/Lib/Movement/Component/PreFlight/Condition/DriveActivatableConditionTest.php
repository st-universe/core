<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\StuTestCase;

class DriveActivatableConditionTest extends StuTestCase
{
    /** @var MockInterface&ActivatorDeactivatorHelperInterface */
    private MockInterface $activatorDeactivatorHelper;

    private PreFlightConditionInterface $subject;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    /** @var MockInterface&ConditionCheckResult */
    private MockInterface $conditionCheckResult;

    #[Override]
    protected function setUp(): void
    {
        $this->activatorDeactivatorHelper = $this->mock(ActivatorDeactivatorHelperInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);
        $this->conditionCheckResult = $this->mock(ConditionCheckResult::class);

        $this->subject = new DriveActivatableCondition($this->activatorDeactivatorHelper);
    }

    public static function provideCheckData(): array
    {
        return [
            [true, false, false, SpacecraftSystemTypeEnum::SYSTEM_IMPULSEDRIVE],
            [false, true, false,  SpacecraftSystemTypeEnum::SYSTEM_WARPDRIVE],
            [false, false, true,  SpacecraftSystemTypeEnum::SYSTEM_TRANSWARP_COIL]
        ];
    }

    #[DataProvider('provideCheckData')]
    public function testCheck(
        bool $isImpulsNeeded,
        bool $isWarpdriveNeeded,
        bool $isTranswarpNeeded,
        SpacecraftSystemTypeEnum $systemType
    ): void {

        $this->flightRoute->shouldReceive('isImpulseDriveNeeded')
            ->withNoArgs()
            ->andReturn($isImpulsNeeded);
        $this->flightRoute->shouldReceive('isWarpDriveNeeded')
            ->withNoArgs()
            ->andReturn($isWarpdriveNeeded);
        $this->flightRoute->shouldReceive('isTranswarpCoilNeeded')
            ->withNoArgs()
            ->andReturn($isTranswarpNeeded);

        $this->activatorDeactivatorHelper->shouldReceive('activate')
            ->with(
                $this->wrapper,
                $systemType,
                $this->conditionCheckResult,
                false,
                true
            )
            ->once();

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }
}

<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Mockery\MockInterface;
use Stu\Component\Game\TimeConstants;
use Stu\Module\Control\StuTime;
use Stu\StuTestCase;

class TradeLicenseTest extends StuTestCase
{
    private TradeLicense $tradeLicense;

    private MockInterface $stuTime;

    #[Override]
    public function setUp(): void
    {
        $this->stuTime = $this->mock(StuTime::class);

        $this->tradeLicense = new TradeLicense();
    }

    public function testGetRemainingFullDays_expectZeroDays_ifAlreadyExpired(): void
    {
        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->tradeLicense->setExpired(42);

        $result = $this->tradeLicense->getRemainingFullDays($this->stuTime);

        $this->assertEquals(0, $result);
    }

    public function testGetRemainingFullDays_expectZeroDays_ifOneSecondMissing(): void
    {
        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->tradeLicense->setExpired(TimeConstants::ONE_DAY_IN_SECONDS - 1);

        $result = $this->tradeLicense->getRemainingFullDays($this->stuTime);

        $this->assertEquals(0, $result);
    }

    public function testGetRemainingFullDays_expectOneDay_ifExactlyOneDayInSecondsLeft(): void
    {
        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->tradeLicense->setExpired(TimeConstants::ONE_DAY_IN_SECONDS);

        $result = $this->tradeLicense->getRemainingFullDays($this->stuTime);

        $this->assertEquals(1, $result);
    }
}

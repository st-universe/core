<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystemMap;
use Stu\StuTestCase;

final class BorgSphereActivityPolicyTest extends StuTestCase
{
    /** @return iterable<string, array{string, bool}> */
    public static function serverTimes(): iterable
    {
        yield 'midnight' => ['00:00:00', true];
        yield 'before rest' => ['00:09:59', true];
        yield 'start of rest' => ['00:10:00', false];
        yield 'during rest' => ['04:30:00', false];
        yield 'end of rest' => ['09:59:59', false];
        yield 'resumes hunting' => ['10:00:00', true];
        yield 'noon' => ['12:00:00', true];
        yield 'before midnight' => ['23:59:59', true];
    }

    #[DataProvider('serverTimes')]
    public function testRestPeriodUsesServerTimeWithInclusiveStartAndExclusiveEnd(string $time, bool $canHunt): void
    {
        self::assertSame($canHunt, $this->policy($time)->canHunt());
    }

    /** @return iterable<string, array{bool, bool, bool}> */
    public static function signatures(): iterable
    {
        yield 'visible ship' => [false, false, false];
        yield 'cloaked ship' => [true, false, true];
        yield 'invisible RP ship' => [false, true, true];
    }

    #[DataProvider('signatures')]
    public function testOnlyVisibleShipsBlockNightlyDestinationsOnBothMaps(bool $cloaked, bool $invisible, bool $expected): void
    {
        $ship = $this->mock(Ship::class);
        $ship->shouldReceive('isCloaked')->andReturn($cloaked);
        $ship->shouldReceive('isRpgModuleInvisible')->andReturn($invisible);
        foreach ([new Map(), new StarSystemMap()] as $location) {
            $location->getSpacecrafts()->add($ship);
            self::assertSame($expected, $this->policy('00:10:00')->canEnter($location));
            self::assertTrue($this->policy('10:00:00')->canEnter($location));
        }
    }

    public function testOldFlightTracesDoNotBlockAnEmptyField(): void
    {
        $location = new Map();
        $location->getSignatures()->add(new FlightSignature());

        self::assertTrue($this->policy('05:00:00')->canEnter($location));
    }

    private function policy(string $time): BorgSphereActivityPolicy
    {
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturn(strtotime('2026-11-09 ' . $time));
        return new BorgSphereActivityPolicy($clock);
    }
}

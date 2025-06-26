<?php

declare(strict_types=1);

namespace Stu\Component\Station\Dock;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class DockPrivilegeUtilityTest extends StuTestCase
{
    private const int USER_ID = 5;
    private const int ALLY_ID = 55;
    private const int FACTION_ID = 555;
    private const int SHIP_ID = 5555;

    private DockPrivilegeUtilityInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->subject = new DockPrivilegeUtility();
    }

    public static function provideUserSourceData(): array
    {
        return [
            [[[DockTypeEnum::USER, 1, DockModeEnum::ALLOW]], false],
            [[[DockTypeEnum::USER, self::USER_ID, DockModeEnum::DENY]], false],
            [[[DockTypeEnum::ALLIANCE, self::USER_ID, DockModeEnum::ALLOW]], false],
            [[[DockTypeEnum::FACTION, self::USER_ID, DockModeEnum::ALLOW]], false],
            [[[DockTypeEnum::SHIP, self::USER_ID, DockModeEnum::ALLOW]], false],
            [[[DockTypeEnum::USER, self::USER_ID, DockModeEnum::ALLOW]], true],
            [[[DockTypeEnum::ALLIANCE, self::ALLY_ID, DockModeEnum::ALLOW]], true],
            [[[DockTypeEnum::FACTION, self::FACTION_ID, DockModeEnum::ALLOW]], true],
            [[[DockTypeEnum::SHIP, self::SHIP_ID, DockModeEnum::ALLOW]], false],
            [[
                [DockTypeEnum::ALLIANCE, self::ALLY_ID, DockModeEnum::DENY],
                [DockTypeEnum::USER, self::USER_ID, DockModeEnum::ALLOW]
            ], false],
            [[
                [DockTypeEnum::FACTION, self::FACTION_ID, DockModeEnum::ALLOW],
                [DockTypeEnum::ALLIANCE, self::ALLY_ID, DockModeEnum::ALLOW]
            ], true],
            [[
                [DockTypeEnum::FACTION, self::USER_ID, DockModeEnum::DENY],
                [DockTypeEnum::ALLIANCE, self::USER_ID, DockModeEnum::DENY],
                [DockTypeEnum::SHIP, self::USER_ID, DockModeEnum::DENY],
                [DockTypeEnum::USER, self::USER_ID, DockModeEnum::ALLOW],
            ], true],
        ];
    }

    #[DataProvider('provideUserSourceData')]
    public function testCheckPrivilegeForUserSource(array $privilegesData, bool $expectedResult): void
    {
        $user = $this->mock(User::class);

        $this->runTestsWithSource($user, $user, $privilegesData, $expectedResult);
    }

    public static function provideShipSourceData(): array
    {
        return [
            [[[DockTypeEnum::USER, 1, DockModeEnum::ALLOW]], false],
            [[[DockTypeEnum::USER, self::USER_ID, DockModeEnum::DENY]], false],
            [[[DockTypeEnum::ALLIANCE, self::USER_ID, DockModeEnum::ALLOW]], false],
            [[[DockTypeEnum::FACTION, self::USER_ID, DockModeEnum::ALLOW]], false],
            [[[DockTypeEnum::SHIP, self::USER_ID, DockModeEnum::ALLOW]], false],
            [[[DockTypeEnum::USER, self::USER_ID, DockModeEnum::ALLOW]], true],
            [[[DockTypeEnum::ALLIANCE, self::ALLY_ID, DockModeEnum::ALLOW]], true],
            [[[DockTypeEnum::FACTION, self::FACTION_ID, DockModeEnum::ALLOW]], true],
            [[[DockTypeEnum::SHIP, self::SHIP_ID, DockModeEnum::ALLOW]], true],
            [[
                [DockTypeEnum::ALLIANCE, self::ALLY_ID, DockModeEnum::DENY],
                [DockTypeEnum::USER, self::USER_ID, DockModeEnum::ALLOW]
            ], false],
            [[
                [DockTypeEnum::FACTION, self::FACTION_ID, DockModeEnum::ALLOW],
                [DockTypeEnum::ALLIANCE, self::ALLY_ID, DockModeEnum::ALLOW]
            ], true],
            [[
                [DockTypeEnum::FACTION, self::USER_ID, DockModeEnum::DENY],
                [DockTypeEnum::ALLIANCE, self::USER_ID, DockModeEnum::DENY],
                [DockTypeEnum::SHIP, self::USER_ID, DockModeEnum::DENY],
                [DockTypeEnum::USER, self::USER_ID, DockModeEnum::ALLOW],
            ], true],
            [[
                [DockTypeEnum::FACTION, self::FACTION_ID, DockModeEnum::DENY],
                [DockTypeEnum::ALLIANCE, self::ALLY_ID, DockModeEnum::ALLOW],
                [DockTypeEnum::SHIP, self::SHIP_ID, DockModeEnum::ALLOW],
                [DockTypeEnum::USER, self::USER_ID, DockModeEnum::ALLOW],
            ], false],
            [[
                [DockTypeEnum::ALLIANCE, self::ALLY_ID, DockModeEnum::DENY],
                [DockTypeEnum::USER, self::USER_ID, DockModeEnum::ALLOW],
            ], false],
            [[
                [DockTypeEnum::USER, self::USER_ID, DockModeEnum::DENY],
                [DockTypeEnum::SHIP, self::SHIP_ID, DockModeEnum::ALLOW],
            ], false],
        ];
    }

    #[DataProvider('provideShipSourceData')]
    public function testCheckPrivilegeForShipSource(array $privilegesData, bool $expectedResult): void
    {
        $user = $this->mock(User::class);
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(self::SHIP_ID);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($user);

        $this->runTestsWithSource($user, $ship, $privilegesData, $expectedResult);
    }

    private function runTestsWithSource(
        User|MockInterface $user,
        User|Ship|MockInterface $source,
        array $privilegesData,
        bool $expectedResult
    ): void {
        $station = $this->mock(Station::class);
        $alliance = $this->mock(Alliance::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(self::USER_ID);
        $user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($alliance);
        $user->shouldReceive('getFactionId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(self::FACTION_ID);

        $alliance->shouldReceive('getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(self::ALLY_ID);

        $privilegeArray = [];
        foreach ($privilegesData as $entry) {
            $privilege = $this->mock(DockingPrivilege::class);

            $privilege->shouldReceive('getPrivilegeType')
                ->withNoArgs()
                ->zeroOrMoreTimes()
                ->andReturn($entry[0]);
            $privilege->shouldReceive('getTargetId')
                ->withNoArgs()
                ->zeroOrMoreTimes()
                ->andReturn($entry[1]);
            $privilege->shouldReceive('getPrivilegeMode')
                ->withNoArgs()
                ->zeroOrMoreTimes()
                ->andReturn($entry[2]);

            $privilegeArray[] = $privilege;
        }

        $station->shouldReceive('getDockPrivileges')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection($privilegeArray));

        $result = $this->subject->checkPrivilegeFor($station, $source);

        $this->assertEquals($expectedResult, $result);
    }
}

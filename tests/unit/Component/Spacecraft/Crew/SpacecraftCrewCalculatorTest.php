<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Crew;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftCrewCalculatorTest extends StuTestCase
{
    private MockInterface&ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository;

    private SpacecraftCrewCalculatorInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->shipRumpCategoryRoleCrewRepository = $this->mock(ShipRumpCategoryRoleCrewRepositoryInterface::class);

        $this->subject = new SpacecraftCrewCalculator(
            $this->shipRumpCategoryRoleCrewRepository
        );
    }

    public function testGetCrewObjExpectNullIfNoRole(): void
    {
        $rump = $this->mock(SpacecraftRump::class);

        $rump->shouldReceive('getRoleId')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->getCrewObj($rump);

        $this->assertNull($result);
    }

    public function testGetCrewObjExpectCaching(): void
    {
        $rump = $this->mock(SpacecraftRump::class);
        $roleCrew = $this->mock(ShipRumpCategoryRoleCrew::class);

        $category = SpacecraftRumpCategoryEnum::DESTROYER;
        $role = SpacecraftRumpRoleEnum::PULSE_SHIP;

        $rump->shouldReceive('getRoleId')
            ->withNoArgs()
            ->andReturn($role);
        $rump->shouldReceive('getCategoryId')
            ->withNoArgs()
            ->andReturn($category);

        $this->shipRumpCategoryRoleCrewRepository->shouldReceive('getByShipRumpCategoryAndRole')
            ->with($category, $role)
            ->once()
            ->andReturn($roleCrew);

        $result1 = $this->subject->getCrewObj($rump);
        $result2 = $this->subject->getCrewObj($rump);

        $this->assertEquals($roleCrew, $result1);
        $this->assertEquals($roleCrew, $result2);
    }

    public function testGetCrewObjExpectCategoryDistinction(): void
    {
        $rump1 = $this->mock(SpacecraftRump::class);
        $rump2 = $this->mock(SpacecraftRump::class);
        $rump3 = $this->mock(SpacecraftRump::class);
        $roleCrew1 = $this->mock(ShipRumpCategoryRoleCrew::class);
        $roleCrew2 = $this->mock(ShipRumpCategoryRoleCrew::class);
        $roleCrew3 = $this->mock(ShipRumpCategoryRoleCrew::class);

        $category1 = SpacecraftRumpCategoryEnum::DESTROYER;
        $category2 = SpacecraftRumpCategoryEnum::CRUISER;
        $roleA = SpacecraftRumpRoleEnum::PULSE_SHIP;
        $roleB = SpacecraftRumpRoleEnum::GREAT_FREIGHTER;

        $rump1->shouldReceive('getRoleId')
            ->withNoArgs()
            ->andReturn($roleA);
        $rump1->shouldReceive('getCategoryId')
            ->withNoArgs()
            ->andReturn($category1);

        $rump2->shouldReceive('getRoleId')
            ->withNoArgs()
            ->andReturn($roleA);
        $rump2->shouldReceive('getCategoryId')
            ->withNoArgs()
            ->andReturn($category2);

        $rump3->shouldReceive('getRoleId')
            ->withNoArgs()
            ->andReturn($roleB);
        $rump3->shouldReceive('getCategoryId')
            ->withNoArgs()
            ->andReturn($category1);

        $this->shipRumpCategoryRoleCrewRepository->shouldReceive('getByShipRumpCategoryAndRole')
            ->with($category1, $roleA)
            ->once()
            ->andReturn($roleCrew1);
        $this->shipRumpCategoryRoleCrewRepository->shouldReceive('getByShipRumpCategoryAndRole')
            ->with($category2, $roleA)
            ->once()
            ->andReturn($roleCrew2);
        $this->shipRumpCategoryRoleCrewRepository->shouldReceive('getByShipRumpCategoryAndRole')
            ->with($category1, $roleB)
            ->once()
            ->andReturn($roleCrew3);

        $result1 = $this->subject->getCrewObj($rump1);
        $this->assertSame($roleCrew1, $result1);

        $result2 = $this->subject->getCrewObj($rump2);
        $this->assertSame($roleCrew2, $result2);

        $result3 = $this->subject->getCrewObj($rump3);
        $this->assertSame($roleCrew3, $result3);
    }
}

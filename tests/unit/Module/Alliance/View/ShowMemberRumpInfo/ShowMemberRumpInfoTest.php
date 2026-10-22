<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowMemberRumpInfo;

use Doctrine\Common\Collections\ArrayCollection;
use JBBCode\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Module\Alliance\Lib\AllianceJobManager;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceJobPermission;
use Stu\Orm\Entity\AllianceMemberJob;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class ShowMemberRumpInfoTest extends StuTestCase
{
    public static function provideMemberships(): array
    {
        return [[false], [true]];
    }

    #[DataProvider('provideMemberships')]
    public function testOnlyCurrentAllianceCanInspectMemberShips(bool $sameAlliance): void
    {
        request::setMockVars(['userid' => '102', 'rumpid' => '7']);
        $manager = $this->mock(AllianceJobManagerInterface::class);
        $users = $this->mock(UserRepositoryInterface::class);
        $rumps = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $stations = $this->mock(StationRepositoryInterface::class);
        $ships = $this->mock(ShipRepositoryInterface::class);
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $context = $this->mock(ViewControllerContext::class);
        $actor = $this->mock(User::class);
        $member = $this->mock(User::class);
        $memberAlliance = $this->mock(Alliance::class);
        $currentAlliance = $this->mock(Alliance::class);
        $memberAlliance->shouldReceive('getId')->andReturn(42);
        $currentAlliance->shouldReceive('getId')->andReturn($sameAlliance ? 42 : 99);
        $context->shouldReceive('getUser')->andReturn($actor);
        $actor->shouldReceive('getAlliance')->andReturn($currentAlliance);
        $users->shouldReceive('find')->with(102)->andReturn($member);
        $member->shouldReceive('getAlliance')->andReturn($memberAlliance);
        if ($sameAlliance) {
            $manager->shouldReceive('hasUserPermission')->with($actor, $memberAlliance, AllianceJobPermissionEnum::VIEW_SHIPS)->once()->andReturnTrue();
            $rump = $this->mock(SpacecraftRump::class);
            $rumps->shouldReceive('find')->with(7)->once()->andReturn($rump);
            $rump->shouldReceive('getName')->andReturn('Schiffe');
            $member->shouldReceive('getName')->andReturn('Mitglied');
            $context->shouldReceive('setPageTitle')->with('Schiffe von Mitglied Mitglied')->once();
            $context->shouldReceive('setMacroInAjaxWindow')->with('html/alliance/alliancememberrumpinfo.twig')->once();
            $stations->shouldReceive('getByUserAndRump')->with($member, $rump)->once()->andReturn([]);
            $ships->shouldReceive('getByUserAndRump')->with($member, $rump)->once()->andReturn([]);
            $result = new ArrayCollection();
            $wrappers->shouldReceive('wrapSpacecrafts')->with([])->once()->andReturn($result);
            $context->shouldReceive('setTemplateVar')->with('WRAPPERS', $result)->once();
        } else {
            $manager->shouldNotReceive('hasUserPermission');
            $rumps->shouldNotReceive('find');
            $ships->shouldNotReceive('getByUserAndRump');
            $stations->shouldNotReceive('getByUserAndRump');
        }
        new ShowMemberRumpInfo($manager, $users, $rumps, new Parser(), $stations, $ships, $wrappers)->handle($context);
    }

    public static function provideAllianceRoles(): iterable
    {
        yield 'no-role' => [null];
        foreach (AllianceJobPermissionEnum::cases() as $permission) {
            yield $permission->name => [$permission];
        }
    }

    #[DataProvider('provideAllianceRoles')]
    public function testOnlyShipVisibilityRolesCanReadShipsAndStations(?AllianceJobPermissionEnum $permission): void
    {
        request::setMockVars(['userid' => '102', 'rumpid' => '7']);
        $assignments = $this->mock(AllianceMemberJobRepositoryInterface::class);
        $users = $this->mock(UserRepositoryInterface::class);
        $rumps = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $stations = $this->mock(StationRepositoryInterface::class);
        $ships = $this->mock(ShipRepositoryInterface::class);
        $wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $context = $this->mock(ViewControllerContext::class);
        $actor = $this->mock(User::class);
        $member = $this->mock(User::class);
        $alliance = $this->mock(Alliance::class);
        $alliance->shouldReceive('getId')->andReturn(42);
        $actor->shouldReceive('getId')->andReturn(101);
        $actor->shouldReceive('getAlliance')->andReturn($alliance);
        $member->shouldReceive('getAlliance')->andReturn($alliance);
        $context->shouldReceive('getUser')->andReturn($actor);
        $users->shouldReceive('find')->with(102)->once()->andReturn($member);
        $job = new AllianceJob()->setAlliance($alliance);
        if ($permission !== null) {
            $job->getPermissions()->add(new AllianceJobPermission()->setJob($job)->setPermission($permission->value));
        }
        $assignment = new AllianceMemberJob()->setUser($actor)->setJob($job);
        $assignments->shouldReceive('getByUser')->with(101)->andReturn([$assignment]);
        $manager = new AllianceJobManager($assignments);
        $allowed = in_array($permission, [AllianceJobPermissionEnum::FOUNDER, AllianceJobPermissionEnum::SUCCESSOR, AllianceJobPermissionEnum::VIEW_SHIPS], true);
        if ($allowed) {
            $rump = $this->mock(SpacecraftRump::class);
            $station = $this->mock(Station::class);
            $ship = $this->mock(Ship::class);
            $rumps->shouldReceive('find')->with(7)->once()->andReturn($rump);
            $rump->shouldReceive('getName')->andReturn('Raumschiffe');
            $member->shouldReceive('getName')->andReturn('Mitglied');
            $context->shouldReceive('setPageTitle')->with('Raumschiffe von Mitglied Mitglied')->once();
            $context->shouldReceive('setMacroInAjaxWindow')->with('html/alliance/alliancememberrumpinfo.twig')->once();
            $stations->shouldReceive('getByUserAndRump')->with($member, $rump)->once()->andReturn([$station]);
            $ships->shouldReceive('getByUserAndRump')->with($member, $rump)->once()->andReturn([$ship]);
            $result = new ArrayCollection();
            $wrappers->shouldReceive('wrapSpacecrafts')->with([$station, $ship])->once()->andReturn($result);
            $context->shouldReceive('setTemplateVar')->with('WRAPPERS', $result)->once();
        } else {
            $rumps->shouldNotReceive('find');
            $stations->shouldNotReceive('getByUserAndRump');
            $ships->shouldNotReceive('getByUserAndRump');
            $wrappers->shouldNotReceive('wrapSpacecrafts');
            $context->shouldNotReceive('setTemplateVar');
            $context->shouldNotReceive('setMacroInAjaxWindow');
        }
        new ShowMemberRumpInfo($manager, $users, $rumps, new Parser(), $stations, $ships, $wrappers)->handle($context);
    }

}

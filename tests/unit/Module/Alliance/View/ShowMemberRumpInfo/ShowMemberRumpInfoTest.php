<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowMemberRumpInfo;

use Doctrine\Common\Collections\ArrayCollection;
use JBBCode\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
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
            $manager->shouldReceive('hasUserPermission')->once()->andReturnTrue();
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
}

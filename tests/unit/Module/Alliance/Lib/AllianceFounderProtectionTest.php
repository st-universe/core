<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Action\DemotePlayer\DemotePlayer;
use Stu\Module\Alliance\Action\DemotePlayer\DemotePlayerRequestInterface;
use Stu\Module\Alliance\Action\KickPlayer\KickPlayer;
use Stu\Module\Alliance\Action\KickPlayer\KickPlayerRequestInterface;
use Stu\Module\Alliance\Action\PromotePlayer\PromotePlayer;
use Stu\Module\Alliance\Action\PromotePlayer\PromotePlayerRequestInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceMemberJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class AllianceFounderProtectionTest extends StuTestCase
{
    public static function provideFounderMutations(): array
    {
        return [['demote', false], ['promote', false], ['kick', false], ['demote', true], ['promote', true], ['kick', true]];
    }

    #[DataProvider('provideFounderMutations')]
    public function testRejectsChangesToFounderBeforeAnySideEffects(string $action, bool $actorIsFounder): void
    {
        $this->runAction($action, true, $actorIsFounder);
    }

    public function testDelegatedManagerCanStillKickOrdinaryMember(): void
    {
        $this->runAction('kick', false, false);
    }

    private function runAction(string $action, bool $targetIsFounder, bool $actorIsFounder): void
    {
        $alliance = $this->mock(Alliance::class);
        $actor = $this->mock(User::class);
        $target = $this->mock(User::class);
        $assignments = $this->mock(AllianceMemberJobRepositoryInterface::class);
        $users = $this->mock(UserRepositoryInterface::class);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $stations = $this->mock(StationRepositoryInterface::class);
        $jobs = $this->mock(AllianceJobRepositoryInterface::class);
        $context = $this->mock(ActionControllerContext::class);
        $alliance->shouldReceive('getId')->andReturn(42);
        $actor->shouldReceive('getId')->andReturn(101);
        $actor->shouldReceive('getAlliance')->andReturn($alliance);
        $target->shouldReceive('getId')->andReturn(102);
        $target->shouldReceive('getAlliance')->andReturn($alliance);
        $users->shouldReceive('find')->with(102)->andReturn($target);
        $context->shouldReceive('getUser')->andReturn($actor);
        foreach ([[101, $actorIsFounder, true], [102, $targetIsFounder, false]] as [$id, $founder, $manage]) {
            $job = $this->mock(AllianceJob::class);
            $assignment = $this->mock(AllianceMemberJob::class);
            $job->shouldReceive('getAlliance')->andReturn($alliance);
            $job->shouldReceive('hasPermission')->with(1)->andReturn($founder);
            $job->shouldReceive('hasPermission')->with(9)->andReturn($manage);
            $assignment->shouldReceive('getJob')->andReturn($job);
            $assignments->shouldReceive('getByUser')->with($id)->andReturn([$assignment]);
        }
        $manager = new AllianceJobManager($assignments);

        if ($targetIsFounder) {
            $assignments->shouldNotReceive('delete');
            $assignments->shouldNotReceive('save');
            $alliance->shouldNotReceive('getMembers');
            $alliance->shouldNotReceive('getStations');
            $target->shouldNotReceive('setAlliance');
            $users->shouldNotReceive('save');
            $this->expectException(AccessViolationException::class);
        } else {
            $alliance->shouldReceive('getMembers')->once()->andReturn(new ArrayCollection([$actor, $target]));
            $alliance->shouldReceive('getStations')->once()->andReturn(new ArrayCollection());
            $alliance->shouldReceive('getName')->andReturn('Allianz');
            $assignments->shouldReceive('delete')->once();
            $target->shouldReceive('setAlliance')->with(null)->once()->andReturnSelf();
            $users->shouldReceive('save')->with($target)->once();
            $messages->shouldReceive('send')->with(UserConstants::USER_NOONE, 102, 'Deine Mitgliedschaft in der Allianz Allianz wurde beendet')->once();
            $context->shouldReceive('setView')->with(Management::VIEW_IDENTIFIER)->once();
            $context->shouldReceive('getInfo->addInformation')->with('Der Siedler wurde rausgeworfen')->once();
        }

        if ($action === 'demote') {
            $request = $this->mock(DemotePlayerRequestInterface::class);
            $request->shouldReceive('getPlayerId')->andReturn(102);
            $handler = new DemotePlayer($request, $messages, $users, $manager);
        } elseif ($action === 'promote') {
            $request = $this->mock(PromotePlayerRequestInterface::class);
            $request->shouldReceive('getPlayerId')->andReturn(102);
            $request->shouldReceive('getPromotionType')->andReturn(7);
            $job = $this->mock(AllianceJob::class);
            $job->shouldReceive('getAlliance')->andReturn($alliance);
            $jobs->shouldReceive('find')->with(7)->andReturn($job);
            $handler = new PromotePlayer($request, $jobs, $messages, $users, $manager);
        } else {
            $request = $this->mock(KickPlayerRequestInterface::class);
            $request->shouldReceive('getPlayerId')->andReturn(102);
            $handler = new KickPlayer($request, $messages, $users, $manager, $stations);
        }
        $handler->handle($context);
    }
}

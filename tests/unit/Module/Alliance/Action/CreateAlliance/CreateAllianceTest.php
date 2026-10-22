<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateAlliance;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class CreateAllianceTest extends StuTestCase
{
    public static function provideMembership(): array
    {
        return [[true], [false]];
    }

    #[DataProvider('provideMembership')]
    public function testCreationRequiresNoExistingAlliance(bool $hasAlliance): void
    {
        $request = $this->mock(CreateAllianceRequestInterface::class);
        $jobs = $this->mock(AllianceJobRepositoryInterface::class);
        $alliances = $this->mock(AllianceRepositoryInterface::class);
        $users = $this->mock(UserRepositoryInterface::class);
        $manager = $this->mock(AllianceJobManagerInterface::class);
        $relations = $this->mock(UserRelationManagerInterface::class);
        $em = $this->mock(EntityManagerInterface::class);
        $context = $this->mock(ActionControllerContext::class);
        $user = $this->mock(User::class);
        $alliance = $this->mock(Alliance::class);
        $context->shouldReceive('getUser')->andReturn($user);
        $user->shouldReceive('getAlliance')->andReturn($hasAlliance ? $alliance : null);
        if ($hasAlliance) {
            $request->shouldNotReceive('getName');
            $alliances->shouldNotReceive('prototype');
            $alliances->shouldNotReceive('save');
            $users->shouldNotReceive('save');
            $em->shouldNotReceive('flush');
            $this->expectException(AccessViolationException::class);
        } else {
            $request->shouldReceive('getName')->andReturn('Testallianz');
            $request->shouldReceive('getDescription')->andReturn('Beschreibung');
            $request->shouldReceive('getFactionMode')->andReturn(0);
            $alliances->shouldReceive('prototype')->once()->andReturn($alliance);
            $alliance->shouldReceive('setName')->with('Testallianz')->once();
            $alliance->shouldReceive('setDescription')->with('Beschreibung')->once();
            $alliance->shouldReceive('setDate')->once();
            $alliances->shouldReceive('save')->with($alliance)->once();
            $jobs->shouldReceive('save')->with(Mockery::type(AllianceJob::class))->twice();
            $relations->shouldReceive('removeRelationsForAllianceEntry')->with($user, $alliance, true)->once();
            $manager->shouldReceive('joinAlliance')->with($user, $alliance)->once();
            $users->shouldReceive('save')->with($user)->once();
            $manager->shouldReceive('assignUserToJob')->with($user, Mockery::on(
                fn (AllianceJob $job): bool => $job->getAlliance() === $alliance && $job->hasPermission(1)
            ))->once();
            $em->shouldReceive('flush')->times(4);
            $em->shouldReceive('refresh')->with($alliance)->once();
            $context->shouldReceive('getInfo->addInformation')->with('Die Allianz wurde gegründet')->once();
        }
        new CreateAlliance($request, $jobs, $alliances, $users, $manager, $relations, $em)->handle($context);
    }
}

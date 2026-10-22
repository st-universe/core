<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SaveJobs;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceJobPermission;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobPermissionRepositoryInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\StuTestCase;

final class SaveJobsTest extends StuTestCase
{
    public static function provideReservedGrants(): array
    {
        return [['7'], ['new_1']];
    }

    #[DataProvider('provideReservedGrants')]
    public function testRejectsReservedGrantBeforeAnyRoleChanges(string $target): void
    {
        $this->runSave([
            ['id' => '7', 'title' => 'Andere Rolle', 'sort' => 2, 'permissions' => [7, 9]],
            ['id' => $target, 'title' => 'Angreiferrolle', 'sort' => 3, 'permissions' => [1, 7, 9]]
        ], false);
    }

    public function testSavesEditablePermissionsAndPreservesFounder(): void
    {
        $this->runSave([
            ['id' => '1', 'title' => 'Präsident', 'sort' => 1, 'permissions' => [1]],
            ['id' => '7', 'title' => 'Verwaltung', 'sort' => 2, 'permissions' => [7, 9]]
        ], true);
    }

    private function runSave(array $data, bool $allowed): void
    {
        $request = $this->mock(SaveJobsRequestInterface::class);
        $jobs = $this->mock(AllianceJobRepositoryInterface::class);
        $permissions = $this->mock(AllianceJobPermissionRepositoryInterface::class);
        $manager = $this->mock(AllianceJobManagerInterface::class);
        $alliances = $this->mock(AllianceRepositoryInterface::class);
        $em = $this->mock(EntityManagerInterface::class);
        $alliance = $this->mock(Alliance::class);
        $user = $this->mock(User::class);
        $context = $this->mock(ActionControllerContext::class);
        $founder = $this->mock(AllianceJob::class);
        $job = $this->mock(AllianceJob::class);
        $founder->shouldReceive('getId')->andReturn(1);
        $founder->shouldReceive('hasPermission')->with(1)->andReturnTrue();
        $job->shouldReceive('getId')->andReturn(7);
        $job->shouldReceive('hasPermission')->with(1)->andReturnFalse();
        $alliance->shouldReceive('getId')->andReturn(42);
        $alliance->shouldReceive('getJobs')->andReturn(new ArrayCollection([$founder, $job]));
        $user->shouldReceive('getAlliance')->andReturn($alliance);
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('setView')->with(Edit::VIEW_IDENTIFIER)->once();
        $manager->shouldReceive('hasUserPermission')->with($user, $alliance, AllianceJobPermissionEnum::MANAGE_JOBS)->andReturnTrue();
        $request->shouldReceive('getJobsData')->andReturn(json_encode($data, JSON_THROW_ON_ERROR));

        if (!$allowed) {
            $jobs->shouldNotReceive('save');
            $jobs->shouldNotReceive('delete');
            $permissions->shouldNotReceive('deleteByJob');
            $permissions->shouldNotReceive('save');
            $em->shouldNotReceive('flush');
            $founder->shouldNotReceive('setTitle');
            $job->shouldNotReceive('setTitle');
            $this->expectException(AccessViolationException::class);
        } else {
            foreach ([$founder, $job] as $role) {
                $role->shouldReceive('getAlliance')->andReturn($alliance);
                $role->shouldReceive('setTitle')->once();
                $role->shouldReceive('setSort')->once();
                $jobs->shouldReceive('save')->with($role)->once();
                $em->shouldReceive('refresh')->with($role)->once();
            }
            $permissions->shouldReceive('deleteByJob')->with(7)->once();
            foreach ([7, 9] as $value) {
                $permissions->shouldReceive('save')->with(Mockery::on(
                    fn (AllianceJobPermission $permission): bool => $permission->getJob() === $job && $permission->getPermission() === $value
                ))->once();
            }
            $em->shouldReceive('flush')->times(3);
            $em->shouldReceive('refresh')->with($alliance)->once();
            $alliances->shouldReceive('find')->with(42)->andReturn($alliance);
            $context->shouldReceive('getInfo->addInformation')->with('Die Allianz-Rollen wurden gespeichert')->once();
        }

        new SaveJobs($request, $jobs, $permissions, $manager, $alliances, $em)->handle($context);
    }
}

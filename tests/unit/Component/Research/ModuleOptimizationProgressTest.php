<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserModuleOptimization;
use Stu\Orm\Repository\UserModuleOptimizationRepositoryInterface;
use Stu\StuTestCase;

final class ModuleOptimizationProgressTest extends StuTestCase
{
    public function testNewConcreteModuleGetsOneImprovementStep(): void
    {
        $user = new User();
        $module = new Module();
        $repository = $this->mock(UserModuleOptimizationRepositoryInterface::class);
        $repository->shouldReceive('get')->with($user, $module)->once()->andReturnNull();
        $repository->shouldReceive('save')->once()->withArgs(static fn (UserModuleOptimization $progress): bool => $progress->getUser() === $user && $progress->getModule() === $module && $progress->getImprovementSteps() === 1);
        self::assertSame(1, new ModuleOptimizationProgress($repository)->improve($user, $module, 0, 70));
    }

    public function testFinishingSameAttemptTwiceDoesNotAddAnotherStep(): void
    {
        $user = new User();
        $module = new Module();
        $progress = new UserModuleOptimization()->setImprovementSteps(13);
        $repository = $this->mock(UserModuleOptimizationRepositoryInterface::class);
        $repository->shouldReceive('get')->with($user, $module)->once()->andReturn($progress);
        self::assertSame(13, new ModuleOptimizationProgress($repository)->improve($user, $module, 12, 70));
    }

    public function testMaximumIsNotExceeded(): void
    {
        $user = new User();
        $module = new Module();
        $progress = new UserModuleOptimization()->setImprovementSteps(70);
        $repository = $this->mock(UserModuleOptimizationRepositoryInterface::class);
        $repository->shouldReceive('get')->with($user, $module)->once()->andReturn($progress);
        self::assertSame(70, new ModuleOptimizationProgress($repository)->improve($user, $module, 70, 70));
    }
}

<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserModuleOptimization;
use Stu\Orm\Repository\UserModuleOptimizationRepositoryInterface;

final class ModuleOptimizationProgress
{
    public function __construct(private UserModuleOptimizationRepositoryInterface $repository) {}

    public function getImprovementSteps(User $user, Module $module): int
    {
        return $this->repository->get($user, $module)?->getImprovementSteps() ?? 0;
    }

    public function improve(User $user, Module $module, int $expectedSteps, int $maxSteps): int
    {
        $progress = $this->repository->get($user, $module)
            ?? new UserModuleOptimization()->setUser($user)->setModule($module);
        if ($progress->getImprovementSteps() === $expectedSteps && $expectedSteps < $maxSteps) {
            $progress->setImprovementSteps($expectedSteps + 1);
            $this->repository->save($progress);
        }
        return $progress->getImprovementSteps();
    }
}

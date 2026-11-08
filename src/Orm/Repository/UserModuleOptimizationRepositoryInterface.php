<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserModuleOptimization;

interface UserModuleOptimizationRepositoryInterface
{
    public function get(User $user, Module $module): ?UserModuleOptimization;

    public function save(UserModuleOptimization $progress): void;

    public function deleteByUser(User $user): void;
}

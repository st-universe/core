<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;
use Stu\StuTestCase;

class GameModuleAccessCheckerTest extends StuTestCase
{
    public function testRegularModuleIsAllowedWithoutRoleChecks(): void
    {
        $roleChecker = $this->mock(GameUserRoleCheckerInterface::class);
        $roleChecker->shouldNotReceive('isNpc');
        $roleChecker->shouldNotReceive('isAdmin');

        $this->assertTrue(
            (new GameModuleAccessChecker($roleChecker))->isAllowed(ModuleEnum::GAME)
        );
    }

    public function testNpcModuleRequiresNpcOrAdmin(): void
    {
        $roleChecker = $this->mock(GameUserRoleCheckerInterface::class);
        $roleChecker->shouldReceive('isNpc')->once()->andReturnFalse();
        $roleChecker->shouldReceive('isAdmin')->once()->andReturnFalse();

        $this->assertFalse(
            (new GameModuleAccessChecker($roleChecker))->isAllowed(ModuleEnum::NPC)
        );
    }

    public function testAdminModuleRequiresAdmin(): void
    {
        $roleChecker = $this->mock(GameUserRoleCheckerInterface::class);
        $roleChecker->shouldReceive('isAdmin')->once()->andReturnTrue();

        $this->assertTrue(
            (new GameModuleAccessChecker($roleChecker))->isAllowed(ModuleEnum::ADMIN)
        );
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;
use Stu\StuTestCase;

class GameModuleAccessCheckerTest extends StuTestCase
{
    public function testRegularModuleIsAllowedWithoutRoleChecks(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldNotReceive('isNpc');
        $game->shouldNotReceive('isAdmin');

        $this->assertTrue(
            (new GameModuleAccessChecker())->isAllowed(ModuleEnum::GAME, $game)
        );
    }

    public function testNpcModuleRequiresNpcOrAdmin(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('isNpc')->once()->andReturnFalse();
        $game->shouldReceive('isAdmin')->once()->andReturnFalse();

        $this->assertFalse(
            (new GameModuleAccessChecker())->isAllowed(ModuleEnum::NPC, $game)
        );
    }

    public function testAdminModuleRequiresAdmin(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('isAdmin')->once()->andReturnTrue();

        $this->assertTrue(
            (new GameModuleAccessChecker())->isAllowed(ModuleEnum::ADMIN, $game)
        );
    }
}

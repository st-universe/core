<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowRenameCrew;

use Mockery\MockInterface;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ShowRenameCrewTest extends StuTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;

    private ShowRenameCrew $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);

        $this->subject = new ShowRenameCrew($this->spacecraftLoader);
    }

    public function testHandleAllowsUplink(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $ship = $this->mock(Spacecraft::class);

        $shipId = 42;
        $userId = 101;
        request::setMockVars(['id' => $shipId]);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('showMacro')
            ->with('html/ship/crew/crewSlot.twig')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('SHIP', $ship)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('USER_ID', $userId)
            ->once();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $this->spacecraftLoader->shouldReceive('getByIdAndUser')
            ->with($shipId, $userId, true, false)
            ->once()
            ->andReturn($ship);

        $this->subject->handle($game);
    }
}

<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ColoniesComponentTest extends StuTestCase
{
    private ColoniesComponent $subject;

    #[Override]
    protected function setUp(): void
    {

        $this->subject = new ColoniesComponent();
    }

    public function testRenderRendersSystemUserWithoutColonies(): void
    {
        $user = $this->mock(User::class);
        $game = $this->mock(GameControllerInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_NOONE);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('setTemplateVar')
            ->with('USER_COLONIES', [])
            ->once();

        $this->subject->setTemplateVariables($game);
    }

    public function testRenderRendersNormalUserWithColonies(): void
    {
        $user = $this->mock(User::class);
        $game = $this->mock(GameControllerInterface::class);
        $colonies = $this->mock(Collection::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $user->shouldReceive('getColonies')
            ->withNoArgs()
            ->once()
            ->andReturn($colonies);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('setTemplateVar')
            ->with('USER_COLONIES', $colonies)
            ->once();

        $this->subject->setTemplateVariables($game);
    }
}

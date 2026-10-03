<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Doctrine\Common\Collections\Collection;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ColoniesComponentTest extends StuTestCase
{
    private ColoniesComponent $subject;

    #[\Override]
    protected function setUp(): void
    {

        $this->subject = new ColoniesComponent();
    }

    public function testRenderRendersSystemUserWithoutColonies(): void
    {
        $user = $this->mock(User::class);
        $game = $this->mock(TemplateInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_NOONE);

        $game->shouldReceive('setTemplateVar')
            ->with('USER_COLONIES', [])
            ->once();

        $this->subject->setTemplateVariables($user, $game);
    }

    public function testRenderRendersNormalUserWithColonies(): void
    {
        $user = $this->mock(User::class);
        $game = $this->mock(TemplateInterface::class);
        $colonies = $this->mock(Collection::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $user->shouldReceive('getColonies')
            ->withNoArgs()
            ->once()
            ->andReturn($colonies);

        $game->shouldReceive('setTemplateVar')
            ->with('USER_COLONIES', $colonies)
            ->once();

        $this->subject->setTemplateVariables($user, $game);
    }
}

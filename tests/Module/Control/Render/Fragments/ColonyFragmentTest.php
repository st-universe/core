<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Doctrine\Common\Collections\Collection;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ColonyFragmentTest extends StuTestCase
{
    private ColonyFragment $subject;

    protected function setUp(): void
    {

        $this->subject = new ColonyFragment();
    }

    public function testRenderRendersSystemUserWithoutColonies(): void
    {
        $user = $this->mock(UserInterface::class);
        $talPage = $this->mock(TalPageInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_NOONE);

        $talPage->shouldReceive('setVar')
            ->with('COLONIES', [])
            ->once();

        $this->subject->render($user, $talPage);
    }

    public function testRenderRendersNormalUserWithColonies(): void
    {
        $user = $this->mock(UserInterface::class);
        $talPage = $this->mock(TalPageInterface::class);
        $colonies = $this->mock(Collection::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $user->shouldReceive('getColonies')
            ->withNoArgs()
            ->once()
            ->andReturn($colonies);

        $talPage->shouldReceive('setVar')
            ->with('COLONIES', $colonies)
            ->once();

        $this->subject->render($user, $talPage);
    }
}

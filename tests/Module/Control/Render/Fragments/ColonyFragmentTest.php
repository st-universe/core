<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\StuTestCase;

class ColonyFragmentTest extends StuTestCase
{
    /** @var ResearColonyRepositoryInterfacechedRepositoryInterface&MockInterface  */
    private MockInterface $colonyRepository;

    private ColonyFragment $subject;

    protected function setUp(): void
    {
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);

        $this->subject = new ColonyFragment($this->colonyRepository);
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
        $this->colonyRepository->shouldReceive('getColonyListForRenderFragment')
            ->with($user)
            ->once()
            ->andReturn([42]);

        $talPage->shouldReceive('setVar')
            ->with('COLONIES', [42])
            ->once();

        $this->subject->render($user, $talPage);
    }
}

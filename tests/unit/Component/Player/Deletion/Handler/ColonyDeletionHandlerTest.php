<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ColonyDeletionHandlerTest extends StuTestCase
{
    private null|MockInterface|ColonyResetterInterface $colonyResetter;


    private PlayerDeletionHandlerInterface $handler;

    #[\Override]
    public function setUp(): void
    {
        $this->colonyResetter = $this->mock(ColonyResetterInterface::class);

        $this->handler = new ColonyDeletionHandler(
            $this->colonyResetter
        );
    }

    public function testDeleteDeletesUser(): void
    {
        $colony = $this->mock(Colony::class);
        $user = $this->mock(User::class);
        $colonyList = $this->mock(Collection::class);

        $user->shouldReceive('getColonies')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyList);

        $colonyList->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn([$colony]);

        $this->colonyResetter->shouldReceive('reset')
            ->with($colony, false)
            ->once();

        $this->handler->delete($user);
    }
}

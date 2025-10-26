<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\Collection;
use Mockery;
use Mockery\MockInterface;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\StuTestCase;

class ColonyDeletionHandlerTest extends StuTestCase
{
    /**
     * @var null|MockInterface|ColonyResetterInterface
     */
    private $colonyResetter;

    /**
     * @var null|MockInterface|ColonyRepositoryInterface
     */
    private $colonyRepository;

    private PlayerDeletionHandlerInterface $handler;

    #[\Override]
    public function setUp(): void
    {
        $this->colonyResetter = $this->mock(ColonyResetterInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);

        $this->handler = new ColonyDeletionHandler(
            $this->colonyResetter,
            $this->colonyRepository
        );
    }

    public function testDeleteDeletesUser(): void
    {
        $colony = Mockery::mock(Colony::class);
        $user = Mockery::mock(User::class);
        $colonyList = Mockery::mock(Collection::class);

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

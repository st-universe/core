<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\UserInterface;
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

    /**
     * @var null|ColonyDeletionHandler
     */
    private $handler;

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
        $colony = Mockery::mock(ColonyInterface::class);
        $user = Mockery::mock(UserInterface::class);

        $this->colonyRepository->shouldReceive('getOrderedListByUser')
            ->with($user)
            ->once()
            ->andReturn([$colony]);

        $this->colonyResetter->shouldReceive('reset')
            ->with($colony, false)
            ->once();

        $this->handler->delete($user);
    }
}

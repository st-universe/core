<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery\MockInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\StuTestCase;

class FleetDeletionHandlerTest extends StuTestCase
{
    private null|FleetRepositoryInterface|MockInterface $fleetRepository;

    private PlayerDeletionHandlerInterface $handler;

    #[\Override]
    public function setUp(): void
    {
        $this->fleetRepository = $this->mock(FleetRepositoryInterface::class);

        $this->handler = new FleetDeletionHandler(
            $this->fleetRepository
        );
    }

    public function testDeleteDeletesFleets(): void
    {
        $user = $this->mock(User::class);

        $this->fleetRepository->shouldReceive('truncateByUser')
            ->with($user)
            ->once();

        $this->handler->delete($user);
    }
}
